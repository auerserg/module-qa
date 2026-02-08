# Magento 2 QA Extension

The module allows QA to:

- Execute built-in Magento commands
- Run Magento Cron tasks
- View the Cron task queue
- Delete, download, and view logs

Built-in mechanism for commands and task crons to filter which commands can be executed.
Task crons are executed through a new command `bin/magento cron:job:run` based on the N98 functionality.

- [Installation](#markdown-header-installation)
- [Configuration](#markdown-header-configuration)
- [Specifications](#markdown-header-specifications)
- [Attributes](#markdown-header-attributes)

## Installation

Installation Method:

### Type 1: Zip file

- Unzip the zip file in `app/code/Superb`
- Enable the module by running `php bin/magento module:enable Superb_QA`
- Apply database updates by running `php bin/magento setup:upgrade`\*
- Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

At the moment, the module is not available for installation via the composer.

## Configuration

The module is not configured.

## Specifications

#### Controllers
##### Commands
`Admin > System > QA Assistant > Commands`
Executes commands, displays the execution output, and allows you to view the execution output of the last 10 commands.

##### Cron
`Admin > System > QA Assistant > Cron`
Executes the cron of the task and displays the command output, also view the log of the last 10 tasks.

##### Cron Schedule
`Admin > System > QA Assistant > Cron Schedule`
Displays all scheduled cron tasks, and their execution time.

##### Logs
`Admin > System > QA Assistant > Logs`
Displays log files in the 'var/log' directory and allows you to download or delete them. The ability to view the contents of logs has also been added.

#### Console commands
```
bin/magento cron:job:run <job>
```
Performs the cron task as if it was scheduled for now
