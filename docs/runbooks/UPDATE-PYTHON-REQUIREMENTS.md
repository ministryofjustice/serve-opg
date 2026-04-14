## Updating Python Requirements (with uv)

To keep pipelines secure and reproducible, all Python dependencies installed with `pip install` must be **fully pinned**. We generate pinned dependency files using **uv**.

uv compiles a `requirements.txt` file from a `requirements.in` file. It pins both:

- direct dependencies
- all transitive (sub)dependencies

---

### Steps

#### 1. Edit the input file

For example in serve we update:

scripts/cleanup/requirements.in

Add only the top-level dependencies we need. Example:

boto3

---

#### 2. Generate the pinned requirements file

Run:

uv pip compile scripts/cleanup/requirements.in -o scripts/cleanup/requirements.txt

This creates a fully pinned `requirements.txt` containing exact versions for all dependencies.

---

### Example output

A compiled `requirements.txt` will look like:

boto3==1.34.162  
botocore==1.34.162  
jmespath==1.0.1  
python-dateutil==2.9.0.post0  
s3transfer==0.10.2  
six==1.16.0  
urllib3==2.2.2

---

### Updating dependencies later

To update dependencies:

1. Modify `requirements.in`
2. Re-run:

uv pip compile scripts/cleanup/requirements.in -o scripts/cleanup/requirements.txt

3. Commit the updated files

---

### Summary

- edit `requirements.in`
- run `uv pip compile`
