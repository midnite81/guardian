# Contributing to Guardian

Thank you for considering contributing to Guardian! Contributions are welcome from everyone, whether it's a bug report,
feature suggestion, documentation improvement, or a code contribution.

## Table of Contents

- [Getting Started](#getting-started)
    - [Issues](#issues)
    - [Pull Requests](#pull-requests)
- [Setting Up Your Development Environment](#setting-up-your-development-environment)
- [Coding Standards](#coding-standards)
- [Running Tests](#running-tests)
- [Documentation](#documentation)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Enhancements](#suggesting-enhancements)

## Getting Started

### Issues

- Before submitting a new issue, please check if it already exists in
  the [issue tracker](https://github.com/midnite81/guardian/issues).
- When creating a bug report, please include as many details as possible. Fill out the required template, the
  information it asks for helps resolve issues faster.
- For larger features, please see the [Suggesting Enhancements](#suggesting-enhancements) section.

### Pull Requests

- Fill in the required template
- Use clear, descriptive titles for your pull requests. If applicable, include the issue number in the PR title (
  e.g., "[#123] Add new feature for X")
- Follow the [PHP PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)
- Include thoughtfully-worded, well-structured [Pest PHP](https://pestphp.com/) tests
- Document new code based on the [PHPDoc](https://docs.phpdoc.org/3.0/guide/guides/docblocks.html) standard
- End all files with a newline
- Before submitting the pull request, run `composer before-pr` and ensure all checks pass

## Setting Up Your Development Environment

1. Fork the repository on GitHub
2. Clone your fork locally

```bash
git clone git@github.com:your-username/guardian.git
```

3. Create a branch for your changes

```bash
git checkout -b my-new-feature
```

4. Install dependencies

```bash
composer install
```

## Coding Standards

The project follows the [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/). You can use Laravel Pint to
ensure your code adheres to the standards:

```bash
composer pint
```

## Running Tests

Guardian uses [Pest PHP](https://pestphp.com/) for testing. To run the test suite:

```bash
composer pest
```

To run tests with coverage:

```bash
composer coverage
```

Please ensure all tests are passing before submitting a pull request.

## Static Analysis

The project uses PHPStan for static analysis. Run it with:

```bash
composer stan
```

## Before Submitting a Pull Request

Before submitting a pull request, run the following command to ensure all checks pass:

```bash
composer before-pr
```

This command will:

1. Run Laravel Pint to fix code style issues
2. Run Pest PHP tests
3. Run PHPStan for static analysis
4. Run tests with coverage and ensure a minimum coverage of 80%

## Documentation

Documentation is a crucial part of Guardian. Please make sure to update the documentation when changing or adding
features. This includes:

- README.md
- PHPDoc comments in the source code
- Any additional documentation in the `docs` folder (if applicable)

## Reporting Bugs

When reporting bugs, please use the bug report template provided. Include as much detail as possible, including steps to
reproduce, expected behavior, and actual behavior.

## Suggesting Enhancements

Enhancement suggestions are welcome. Please use the feature request template when suggesting new features. Explain the
feature in detail, its use cases, and how it would benefit Guardian users.

For large feature requests or significant changes to the project structure, please get in contact with the maintainers
first. Some features might be outside the scope of the project, and discussing them beforehand can save time and effort.

Thank you for contributing to Guardian!