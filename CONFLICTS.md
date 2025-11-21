# Resolving public/index.php merge conflicts

When updating this branch with upstream changes, conflicts often appear on `public/index.php`. The file was heavily rewritten, so Git cannot automatically reconcile newer edits from other branches.

## Why conflicts appear
- The upstream branch still contains the previous implementation of `public/index.php`, while this branch has a fully rebuilt frontend.
- Even small upstream edits to the old file collide with the rebuilt version because Git sees the entire file as changed.

## How to resolve
1. Fetch the latest upstream branch (replace `main` with the actual target branch):
   ```bash
   git fetch origin
   git checkout work
   git merge origin/main
   ```
2. Keep the rebuilt frontend from this branch by accepting **ours** for `public/index.php`:
   ```bash
   git checkout --ours public/index.php
   ```
   If you need upstream changes, re-apply them manually after checking out `--ours`.
3. Stage and continue the merge:
   ```bash
   git add public/index.php
   git commit
   ```
4. Push the merged branch:
   ```bash
   git push
   ```

If you prefer the upstream version instead, use `git checkout --theirs public/index.php` in step 2.
