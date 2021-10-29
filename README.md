# PHP Actions

![GitHub Actions](https://github.com/saundefined/php-actions/workflows/build/badge.svg)
[![codecov](https://codecov.io/gh/saundefined/php-actions/branch/master/graph/badge.svg?token=9IASF9M5XC)](https://codecov.io/gh/saundefined/php-actions)

Package with console commands for PHP team.

## Commands

### Deploy documentation build to Netlify (``documentation:deploy``)

| Parameter | Required | Type | Description |
|-----------|----------|------|-------------|
| file | yes | string | path to documentation build file |
| netlify-site | yes | string | Netlify site url |
| netlify-token | yes | string | Netlify access token |
| repository | yes | string | GitHub org/repository |
| issue | yes | int | GitHub Issue/PR number |
| github-token | yes | string | GitHub access token |
| commit | yes | string | Related commit hash |

#### Example

```bash
./bin/action documentation:deploy \
          --file='output/php-chunked-xhtml.zip' \
          --netlify-site=php-doc-en.netlify.app \
          --netlify-token=my-secret-token \
          --repository="php/doc-en" \
          --issue=1 \
          --github-token=my-secret-token \
          --commit=0bbcee7822ad480c99e13ce28a72a0e97fe68c05
```
