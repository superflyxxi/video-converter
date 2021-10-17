# Docker Linting
Docker linting is done to follow best practices. See the `.hadolint.yml` for rules to ignore.

`docker run --rm -i hadolint/hadolint < Dockerfile`

# Formatting
PHP formatting follows the rules of prettier. Follow these intructions locally to running prettier.

Install the tools. This is a one time run locally.
`docker run --rm -it -v "$(pwd):/pwd" -w /pwd --entrypoint npm node install prettier @prettier/plugin-php`

Now run each time to fix formatting.
`docker run --rm -it -v "$(pwd):/pwd" -w /pwd --entrypoint npx prettier src/ tests/ --write`

