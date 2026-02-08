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

<img width="800" alt="QA-Assistant-Commands-Magento-Admin" src="https://github.com/user-attachments/assets/2c9df2ba-df29-43d3-a37f-41133f3e834f" />

##### Cron
`Admin > System > QA Assistant > Cron`

Executes the cron of the task and displays the command output, also view the log of the last 10 tasks.

<img width="800" alt="QA-Assistant-Cron-Magento-Admin" src="https://github.com/user-attachments/assets/745eb19c-abe0-4655-94df-bda9369e8912" />

##### Cron Schedule
`Admin > System > QA Assistant > Cron Schedule`

Displays all scheduled cron tasks, and their execution time.

<img width="800" alt="QA-Assistant-Cron-Schedule-Magento-Admin" src="https://github.com/user-attachments/assets/c6ecf9b2-7404-4af8-9d48-53e7cfef6aac" />

##### Logs
`Admin > System > QA Assistant > Logs`

Displays log files in the 'var/log' directory and allows you to download or delete them.

<img width="800" alt="QA-Assistant-Logs-Magento-Admin" src="https://github.com/user-attachments/assets/67610f54-ce5f-4632-a884-f0f4fc3df6a2" />

The ability to view the contents of logs has also been added.

<img width="800" alt="QA-Assistant-Log-View-cron-log-Magento-Admin" src="https://github.com/user-attachments/assets/0dfe3160-fc0d-47b1-9fee-ac9914513e95" />

#### Console commands
```
bin/magento cron:job:run <job>
```
Performs the cron task as if it was scheduled for now
