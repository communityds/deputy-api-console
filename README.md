# Deputy API Console

The repository provides a command line interface tool to debug or inspect the Deputy API via the [Deputy API Wrapper](https://github.com/communityds/deputy-api-wrapper).

## Installation

Clone this repository to your development environment:

```bash
git clone https://github.com/communityds/deputy-api-console.git
```

Within the cloned repository, install all Composer packages:

```bash
composer install
```

Copy the configuration template and customise the configuration to include the authentication and target details of the wrapper:

```bash
cp config.php.dist config.php
```

Run the `me` command to confirm the configuration looks correct:

```bash
./console me
```

Information regarding the user should appear.

## Usage

Run the `./console` command to display the usage information to confirm things are working as expected.

To view raw responses from the Deputy API when running any command, increase the verbosity of the command by passing in the `-vvv` option. For example:

```bash
./console me -vvv
```

## Debugging Schema

To determine what schema is being used by the wrapper, run the following command:

```bash
./console schema RESOURCE
# e.g. ./console schema Company
```

The output of this command includes both what is returned via the `INFO` endpoints as well as any customisations made to the schema.
Any difference between the schemas is indicated by the `*` character.

## Debugging Records

To view the details of a particular record, run the following command:

```bash
./console record RESOURCE ID
# e.g. ./console record Company 1
```

The output will show what fields and relationships are available for the record.

## Generating PHPDoc

The top section of each model class includes a series of `@property` PHPDoc tags.
These reflect the fields, joins and associations that are reported by the Resource `INFO` endpoints.
If there is a change to these, then run the following command to generate the tags for that model and copy and paste that into the model class.

```bash
./console phpdoc RESOURCE
# e.g. ./console phpdoc Company
```

Note that there may be some conflicting property names.
Use the existing tags of the model to determine how to resolve the issue.

Also ensure that any custom properties (i.e. those with get or set methods) should remain.
