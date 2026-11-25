# GitHub Actions and GitHub Marketplace Repair Record

Date: 2026-11-25  
Repository: `SHIVAM73566/VentureX-ERP-CRM`  
Action repository: `SHIVAM73566/venturex-deploy-action`

This document records evidence from public GitHub API/website reads. No token, secret, or authenticated-only data was printed.

## GitHub Actions findings

Public API read of the 100 most recent workflow runs returned 182 total runs, with 29 failed/cancelled in that page:

```text
CI failures=15
Deploy GitHub Pages failures=3
pages build and deployment failures=11
```

Latest failed runs inspected:

```text
CI #62                 run 45431931659 failed step: Check code style (Pint)
Deploy GitHub Pages #44 run 45431931618 failed step: Deploy to GitHub Pages
pages build and deployment #46 run 45431931078 failed step: Checkout
```

Annotations reported:

```text
CI: process exited 1 at Pint check
legacy Pages: No url found for submodule path 'release/venturex-deploy-action' in .gitmodules
Deploy Pages: No artifacts named "github-pages" were found for this workflow run
```

## Fixes applied locally

| Problem | Minimal fix | Verification |
|---|---|---|
| Pint style debt causing CI failures | Formatted only the 16 flagged PHP files | `PINT_TEST_EXIT=0`, `{"tool":"pint","result":"passed"}` |
| CI only ran on `main` | Added `layers-v1` and `phase0-baseline-audit` to push/PR triggers; added `permissions: contents: read`; added PHP syntax step | `WORKFLOWS_YAML_OK`; existing workflow structure preserved |
| Submodule gitlink had no `.gitmodules`, breaking legacy Pages checkout | Added `.gitmodules` pointing to `https://github.com/SHIVAM73566/venturex-deploy-action.git` | `git config -f .gitmodules` parsed; `git submodule sync` exit 0 |
| Two Pages workflows shared concurrency group `pages` | Changed repo workflow group to `venturex-pages-workflow` | `PAGES_YAML_OK` |
| Deploy Pages artifact race in a single job | Split build/upload and deploy into separate jobs; removed mismatched explicit upload name | `PAGES_YAML_OK`; fresh remote run still pending |
| Non-blocking secret scan in security workflow | Made tracked-file scan blocking and added Stripe patterns | Local equivalent scan returned no matches |

No UI, Blade, CSS, JavaScript, layout, navigation, AppSumo pricing, customer, CRM, sales, finance, HR, or ERP data was changed by these fixes.

## GitHub Marketplace findings

The public Marketplace listing loads successfully:

```text
URL=https://github.com/marketplace/actions/venturex-erp-crm-deploy
HTTP_STATUS=200
```

Action metadata at the tested commit validates:

```text
ACTION_NAME=VentureX ERP & CRM Deploy
DESCRIPTION_LENGTH=113
AUTHOR_PRESENT=True
BRANDING_ICON_VALID=True
BRANDING_COLOR_VALID=True
RUNS_USING=composite
INPUT_COUNT=14
README_EXISTS=True
```

Action repository CI runs inspected: 10 runs, 0 failures.

The concrete Marketplace problem is version drift:

```text
Marketplace latest version=v1.0.2
GitHub releases=v1.0.2, v1.0.1, v1.0.0
Action repo README claim=Verified on v1.3.0
Action repo main commit=50e974b "Update Marketplace metadata for verified v1.3.0"
```

A `v1.3.0` annotated tag was created at `50e974b` and pushed successfully:

```text
PUSH_TAG_EXIT=0
REMOTE_TAG=V1.3.0 PRESENT
```

However, GitHub Marketplace uses a published GitHub Release with the Marketplace checkbox, not a tag alone. After the tag push, the release list still showed only v1.0.2/v1.0.1/v1.0.0.

## Marketplace remaining manual step

Publishing the release requires the repository owner to be authenticated in the GitHub UI or `gh` with two-factor authentication:

```text
gh auth status exit=1
GH_TOKEN=missing
GITHUB_TOKEN=missing
```

No token was requested, printed, or stored.

Required maintainer action:

1. Open `https://github.com/SHIVAM73566/venturex-deploy-action/releases/new`.
2. Select tag `v1.3.0` at commit `50e974b`.
3. Title: `v1.3.0 - Verified deployment action`.
4. Enable `Publish this Action to the GitHub Marketplace`.
5. Choose a primary category such as `Deployment`.
6. Confirm `Everything looks good!`, then publish with 2FA.
7. Re-open the Marketplace listing and confirm `v1.3.0` is shown as latest.

Until that release is published, the Marketplace listing will continue to show v1.0.2. This is a blocker, not a code failure.

## Remote verification

Observed on the public GitHub Actions page for latest `main` commit `d36fc7b`:

```text
CI #65=completed successfully
Deploy GitHub Pages #46=completed successfully
Security Audit #32=completed successfully
pages build and deployment #48=completed successfully
```

Legacy Pages checkout failure is gone after adding `.gitmodules`. The custom Pages deployment succeeds after splitting build/upload and deploy into separate jobs.

## Verification boundary

Remote green status above applies to commit `d36fc7b`. Older failed runs remain in history but are superseded. Marketplace release publication remains blocked on maintainer authentication and 2FA; no token was requested or printed.
