
import urllib.request
import boto3
import argparse
import json
import os


class IngressManager:
    aws_account_id = ''
    aws_iam_session = ''
    aws_ec2_client = ''
    security_groups = []

    def __init__(self, config_file, vpc_scope):
        self.vpc_scope = vpc_scope  # 'serveopg' or 'main'
        self.read_parameters_from_file(config_file)
        self.set_iam_role_session()
        self.aws_ec2_client = boto3.client(
            'ec2',
            region_name='eu-west-1',
            aws_access_key_id=self.aws_iam_session['Credentials']['AccessKeyId'],
            aws_secret_access_key=self.aws_iam_session['Credentials']['SecretAccessKey'],
            aws_session_token=self.aws_iam_session['Credentials']['SessionToken'])

        self.vpc_id = self.get_target_vpc_id()

    def read_parameters_from_file(self, config_file):
        with open(config_file) as json_file:
            parameters = json.load(json_file)
            self.aws_account_id = parameters['account_id']
            self.security_groups = [
                parameters['load_balancer_security_group_name']]

    def set_iam_role_session(self):
        if os.getenv('CI'):
            role_arn = 'arn:aws:iam::{}:role/serve-opg-ci'.format(
                self.aws_account_id)
        else:
            role_arn = 'arn:aws:iam::{}:role/operator'.format(
                self.aws_account_id)

        sts = boto3.client(
            'sts',
            region_name='eu-west-1',
        )
        session = sts.assume_role(
            RoleArn=role_arn,
            RoleSessionName='checking_ecs_task',
            DurationSeconds=900
        )
        self.aws_iam_session = session

    def get_ip_addresses(self):
        host_public_cidr = urllib.request.urlopen(
            'https://checkip.amazonaws.com').read().decode('utf8').rstrip() + "/32"
        return host_public_cidr

    def get_target_vpc_name_prefix(self):
        # Map user-friendly input to the actual prefix we search for
        if self.vpc_scope.lower() == "serveopg":
            return "ServeOPG"
        if self.vpc_scope.lower() == "main":
            return "main"
        raise ValueError("vpc_scope must be 'serveopg' or 'main'")

    def get_target_vpc_id(self):
        prefix = self.get_target_vpc_name_prefix()

        # Get all VPCs and check their Name tag
        resp = self.aws_ec2_client.describe_vpcs()
        matches = []
        for vpc in resp.get("Vpcs", []):
            name = None
            for tag in vpc.get("Tags", []) or []:
                if tag.get("Key") == "Name":
                    name = tag.get("Value")
                    break
            if name and name.startswith(prefix):
                matches.append((name, vpc["VpcId"]))

        if not matches:
            raise RuntimeError(f"No VPC found with Name tag starting with '{prefix}'")

        if len(matches) > 1:
            # If you have multiple matches, pick deterministically (sorted by Name).
            matches.sort(key=lambda x: x[0])
            print(f"Multiple VPCs matched '{prefix}': {matches}. Using first: {matches[0]}")

        chosen = matches[0]
        print(f"Using VPC: {chosen[0]} ({chosen[1]})")
        return chosen[1]

    def get_security_group(self, sg_name):
        # Look up SG by group-name within the selected VPC
        resp = self.aws_ec2_client.describe_security_groups(
            Filters=[
                {"Name": "vpc-id", "Values": [self.vpc_id]},
                {"Name": "group-name", "Values": [sg_name]},
            ]
        )
        sgs = resp.get("SecurityGroups", [])
        if not sgs:
            raise RuntimeError(f"Security group '{sg_name}' not found in VPC {self.vpc_id}")

        if len(sgs) > 1:
            # Extremely unlikely inside one VPC, but handle anyway
            raise RuntimeError(f"Multiple security groups named '{sg_name}' found in VPC {self.vpc_id}")

        return resp

    def clear_all_ci_ingress_rules_from_sg(self):
        for sg_name in self.security_groups:
            sg = self.get_security_group(sg_name)['SecurityGroups'][0]
            group_id = sg["GroupId"]

            for ip_permissions in sg.get('IpPermissions', []):
                for rule in ip_permissions.get('IpRanges', []):
                    if rule.get('Description') == "ci ingress":
                        print("found ci ingress rule in " + sg_name)
                        try:
                            print("Removing security group ingress rule " + str(rule) + " from " +
                                  sg_name)
                            self.aws_ec2_client.revoke_security_group_ingress(
                                GroupId=group_id,
                                IpPermissions=[
                                    {
                                        'FromPort': ip_permissions.get('FromPort'),
                                        'IpProtocol': ip_permissions['IpProtocol'],
                                        'IpRanges': [rule],
                                        'ToPort': ip_permissions.get('ToPort'),
                                    },
                                ],
                            )
                            if self.verify_ingress_rule(sg_name):
                                print(
                                    "Verify: Found security group rule that should have been removed from " + str(sg_name))
                                exit(1)
                        except Exception as e:
                            print(e)
                            exit(1)

    def verify_ingress_rule(self, sg_name):
        sg = self.get_security_group(sg_name)['SecurityGroups'][0]
        for ip_permissions in sg.get('IpPermissions', []):
            for sg_rule in ip_permissions.get('IpRanges', []):
                if sg_rule.get('Description') == "ci ingress":
                    print(sg_rule)
                    return True
        return False

    def add_ci_ingress_rule_to_sg(self, ingress_cidr):
        self.clear_all_ci_ingress_rules_from_sg()
        try:
            for sg_name in self.security_groups:
                sg = self.get_security_group(sg_name)['SecurityGroups'][0]
                group_id = sg["GroupId"]

                print("Adding SG rule to " + sg_name)
                self.aws_ec2_client.authorize_security_group_ingress(
                    GroupId=group_id,
                    IpPermissions=[
                        {
                            'FromPort': 443,
                            'IpProtocol': 'tcp',
                            'IpRanges': [
                                {
                                    'CidrIp': ingress_cidr,
                                    'Description': 'ci ingress'
                                },
                            ],
                            'ToPort': 443,
                        },
                    ],
                )
                if self.verify_ingress_rule(sg_name):
                    print("Added ingress rule to " + str(sg_name))
        except Exception as e:
            print(e)


def main():
    parser = argparse.ArgumentParser(
        description="Add or remove your host's IP address to the loadbalancer ingress rules.")

    parser.add_argument("config_file_path", type=str,
                        help="Path to config file produced by terraform")

    parser.add_argument(
        "--vpc-scope",
        choices=["serveopg", "main"],
        default="serveopg",
        help="Which VPC name prefix to search for (default: serveopg). Searches Name tag starting with 'ServeOPG' or 'main'."
    )

    parser.add_argument('--add', dest='action_flag', action='store_const',
                        const=True, default=False,
                        help='add host IP address to security group ci ingress rule (default: remove all ci ingress rules)')

    args = parser.parse_args()

    work = IngressManager(args.config_file_path, args.vpc_scope)
    ingress_cidr = work.get_ip_addresses()
    if args.action_flag:
        work.add_ci_ingress_rule_to_sg(ingress_cidr)
    else:
        work.clear_all_ci_ingress_rules_from_sg()


if __name__ == "__main__":
    main()
