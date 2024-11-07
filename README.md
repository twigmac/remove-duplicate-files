![build status](https://github.com/twigmac/remove-duplicate-files/actions/workflows/build-workflow.yml/badge.svg) ![release status](https://github.com/twigmac/remove-duplicate-files/actions/workflows/release-workflow.yml/badge.svg)

# Remove Duplicate Files

Recursively find and remove duplicate files on the command line.

## Requirements

You will need PHP 8.2 or higher to run the script.

## Standalone Usage

Use either of the following commands to get usage instructions.

```
# use the entrypoint script
bin/remove-duplicate-files --help

# use the Phar file
bin/remove-duplicate-files.phar --help
```

## Usage as Vendor Package

When using it as a vendor package, first install it.

```
composer require twigmac/remove-duplicate-files
```

After installing the package, you can run the binary from the vendor directory.

```
vendor/bin/remove-duplicate-files --help
```

## Usage Examples

```
# This will find up to 100 duplicate files in the second directory but will not remove anything (dry-run).
bin/remove-duplicate-files --verbose /home/example/Downloads/Photos /home/example/Backups/Photos

# This will remove up to 5000 duplicate files in the second directory and will keep them in the first directory
# but it will ask you for permission before each file.
bin/remove-duplicate-files --verbose --limit=5000 --really /home/example/Downloads/Photos /home/example/Backups/Photos

# This will remove up to 5000 duplicate files in the second directory and will keep them in the first directory
# without asking for permission.
bin/remove-duplicate-files --verbose --limit=5000 --really --force /home/example/Downloads/Photos /home/example/Backups/Photos
```
