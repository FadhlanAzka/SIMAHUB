# SIMAHUB Git Workflow Documentation

This document outlines the standard Git workflow for the SIMAHUB project to ensure clean collaboration between developers.

## 🌿 Branching Strategy
We use four main branches for this project:
- **`main`**: The production-ready code. **Never push directly to main.**
- **`staging`**: The pre-production testing environment. All dev branches merge here first to test if they work together.
- **`dev/padlan`**: Padlan's personal development branch.
- **`dev/akbar`**: Akbar's personal development branch.

## 🔒 Branch Protection Rules & Permissions

To maintain code integrity, the following rules are enforced (via GitHub Settings):

1. **`dev/*` Branches**: 
   - Cannot push directly to `main` or `staging`.
   - Cannot push directly to another developer's `dev/*` branch.
   - Can only push to their respective remote branch (e.g., `dev/padlan` pushes to `origin dev/padlan`).
2. **`staging` Branch**:
   - Cannot push directly to `main`.
   - Can only receive code via **Pull Requests** from `dev/*` branches.
3. **`main` Branch**:
   - Cannot push anywhere.
   - Can only receive code via **Pull Requests** from the `staging` branch. 
   - Direct commits/pushes to `main` are completely blocked.

---

## 👨‍💻 1. Daily Development (Push & Pull)

When you start working for the day on your feature, follow these steps. (Assuming you are on `dev/padlan`):

### Ensure you are on your branch
```bash
git checkout dev/padlan
```

### Pull the latest changes
If you are working across multiple computers, make sure your local branch is up to date:
```bash
git pull origin dev/padlan
```

### Write your code, then Add and Commit
After you finish a feature or fix a bug:
```bash
# Add all changed files
git add .

# Write a descriptive commit message
git commit -m "Add new student dashboard layout"
```

### Push to GitHub
```bash
git push origin dev/padlan
```

---

## 🔄 2. Syncing with Other Developers (Merging Staging into Dev)

If Akbar has added a new feature to `staging` and you need his code in your branch (`dev/padlan`) to continue your work:

```bash
# 1. Make sure your local staging branch is up to date
git checkout staging
git pull origin staging

# 2. Go back to your dev branch
git checkout dev/padlan

# 3. Merge staging into your branch
git merge staging

# (Resolve any merge conflicts if they appear, then commit and push)
git push origin dev/padlan
```

---

## 🚀 3. Submitting Your Work (Merging Dev to Staging)

When your feature on `dev/padlan` is complete and tested locally, it's time to merge it into `staging` so it can be combined with Akbar's work.

### Method A: Pull Request (Recommended)
1. Go to the SIMAHUB repository on GitHub.
2. Click **"Compare & pull request"** next to your `dev/padlan` branch.
3. Set the base branch to **`staging`**.
4. Review the changes and click **"Create pull request"**.
5. Once reviewed, click **"Merge pull request"**.

### Method B: Terminal Merge
If you prefer doing it manually in the terminal:
```bash
# 1. Switch to staging and pull the latest code
git checkout staging
git pull origin staging

# 2. Merge your dev branch into staging
git merge dev/padlan

# 3. Push the updated staging to GitHub
git push origin staging

# 4. Switch back to your dev branch to continue working on new things
git checkout dev/padlan
```

---

## 🏆 4. Deploying to Production (Merging Staging to Main)

Once everything in `staging` has been tested and confirmed to work perfectly without bugs, it gets merged into `main`.

**This should strictly be done via a GitHub Pull Request:**
1. Open a Pull Request from `staging` into `main`.
2. Have another developer review it.
3. Merge it.
4. **Never** write new code directly on the `main` branch.

git checkout dev/padlan
git pull origin staging -> make sure there is no local code update first

# ...writes code...
git add .
git commit -m "Padlan finished the API endpoints"
git push origin dev/padlan

git checkout staging
git pull origin staging

# Merge Padlan's local branch AND Akbar's remote branch simultaneously
git merge dev/padlan origin/dev/akbar