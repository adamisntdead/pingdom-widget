![Pingdom Widget](resources/img/plugin-logo.png)

A CraftCMS Widget To Show Stats From Pingdom On The Dashboard


## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require adamisntdead/pingdom

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Pingdom.

## Pingdom Overview

This is a plugin to let you see your sites uptime and average response times for the last week,
without needing to go to the CMS dashboard

## Configuring Pingdom

You are going to need to add the plugin to the dashboard.
Once you do that, you can add the details of your pingdom account, including
an `app_key`, which you can get off [this page](https://my.pingdom.com/account/appkeys).

You will also need to enter the name of the site. It should match what is in pingdom,
for example if you enter `example.com` when the name of the site in pingdom is `www.pingdom.com`, an error will happen.


## Pingdom Roadmap

Some things to do, and ideas for potential features:

* More Stats
* Choose Intervals
* Graphs
* Limit the changing (for agencies with multiple properties)

Brought to you by [Adam Kelly](https://adamisntdead.com)
