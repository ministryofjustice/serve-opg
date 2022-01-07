# Pull Request Procedure

Our setup for creating a pull request and merging code into our master branch is fairly standard and works as follows:

- Create a branch and name it after the ticket number from Jira. It's best to not add any description to
this as it is used in the naming of terraform resources. Example: `DDPB-1234`

- Spin up your local environment as specified in the readme file at the root of this repo

- Make the code changes required by your story in Jira and check they are working in your local environment and all tests are passing locally

- Use git to add your files (`git add <files*>`) and make a commit that is concise and in the imperative format and
starts with the branch name. Example `git commit -m 'DDPB-1234 add scheduling of document task feature'`

- When you are ready, make a draft pull request in github. This kicks off the workflow
that will build the environment in AWS and run unit and integration tests

- If all the tests pass, and it is ready for approval, then complete the pull request template. If the ticket needs further work each new push to github will re-run the pipeline

- Put a link to your Jira ticket in the Dev channel `opg-digideps-devs`. This should have automatically linked
through to your ticket so your colleagues can go and review your code

- Make any changes that are requested and when you have been given PR approval then move the ticket
across the board on Jira to acceptance where it can be signed off by a product manager. If the ticket can't be looked
at by a project manager (internal infrastructure change for example) then proceed to next step.

- Move your ticket across the board to `ready to merge` in Jira.

- At this point you should tidy up your PR commit message to adhere to the following guidelines:

```
- Prepend the commit message with the branch name
- Separate subject from body with a blank line
- Limit the subject line to 50 characters
- Capitalize the subject line
- Do not end the subject line with a period
- Use the imperative mood in the subject line
- Wrap the body at 72 characters
- Use the body to explain what and why vs. how
```

- Once your commit messgae is ready, hit the squash and merge button to combine your commits down to a single commit that will be added to master

- Serve-OPG has a Continuous Delivery pipeline, so merging your ticket in to master will flow through to a release of the code to live

- Move your ticket to `release` on the Jira board.
