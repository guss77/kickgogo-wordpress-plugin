# KickGoGo
Contributors: Guss77
Tags: payments, funding
Requires at least: 4.8.0
Tested up to: 4.9.5
Requires PHP: 7.0.0
Stable tag: 1.0.4
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Crowd-funding functionality for Wordpress sites

## Description

Create campaigns, add pay buttons to pages or posts and track campaign status.

Supported payment processors:

 - [Pelepay](http://pelepay.co.il/)

Ask for additional payment processors and I'll add them.

## Installation

### Minimum Requirements:

 * WordPress 4.8 or greater
 * PHP version 7.9 or greater

### Automatic installation

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. 
To do an automatic install of KickGoGo, log in to your WordPress dashboard, navigate to the Plugins menu and click “Add New”.

In the search field type “KickGoGo” and you should see this plugin and you can install it by simply clicking “Install Now”.

After the install complete, go to the "Plugins" page, find the KickGoGo entry and click "Activate".

### Manual installation

Download the latest release from the Wordpress plugin page by klicking the "Download" button, then upload the ZIP file to your
server and extract it in the `plugins` directory.

After that, go to the "Plugins" page in your Wordpress dashboard, find the KickGoGo entry and click "Activate".

### Updating

Automatic updates should work just fine.

## Usage

1. Login to your Wordpress administration panel.
1. Go to **Plugins** and activate Kickgogo.
1. Go to **Options** -> **Kickgogo** and set up your Pelepay account ID.
1. Go to the **Kickgogo** main page and create a new campaign. Set the name to be displayed to the users in the processor payment page and the target goal. Its also possible to specify a default pay amount - it makes it easier if you don't want to create multiple pay buttons.
1. Go to the page or post where you want to put the payment button and add a button by using the short code `[kickgogo]` (see details below).
1. Go to the page or post where you want to display the status of your campaign and use the short code `[kickgogo-status]` or the other display short codes (see details below).

### Shortcodes

#### Payment Button

Use the `[kickgogo]` short code to create a payment button. 

The short code takes the following parameters:

 * `name` (**Required**) - Specify the name of the campaign this button will apply to. Can also be the numeric ID of the campaign as shown in the campaign list. If this is not specified or an invalid value is specified, the short code will show an error message instead of the button.
 * `amount` (Optional) - Specify the amount to be payed into the campaign. If this is not specified, then the default pay amount set on the campaign will be used instead. If this is not specified and the campaign has no default amount set, the short code will show an error message instead of the button.

The shortcode also requires content which will be used as the text on the payment button. If no content is specified, the default text "Donate" is shown instead.

Example:

```
[kickgogo name=4 amount=25]Donate ₪25 to my campaign[/kickgogo]
```

#### Campaign Status Display

Use the `[kickgogo-status]` short code to create a simple text display of the current status of the campaign.

The short code takes the following parameters:

 * `name` (**Required**) - Specify the name of the campaign for which to display the status. Can also be the numeric ID of the campaign as shown in the campaign list. If this is not specified or an invalid value is specified, the short code will show an error message instead of the status.

The shortcode accepts content to format the status text. The format string must comply with PHP's `[sprintf](http://php.net/sprintf)` format argument and must support 3 arguments, in the following order: current amount, goal, and percentage completed. If the short code is used without content, the default format is used instead, which is: `%d of %d (%d%%)`.

Example:

```
[kickgogo-status]So far collected ₪%d out of ₪%d needed[/kickgogo-status]
```

#### Current Campaign Goal

Use the `[kickgogo-goal]` short code to show the current goal of the campaign.

The short code takes the following parameters:

 * `name` (**Required**) - Specify the name of the campaign for which to display the goal. Can also be the numeric ID of the campaign as shown in the campaign list. If this is not specified or an invalid value is specified, the short code will show an error message instead of the goal.

#### Current Campaign Funds

Use the `[kickgogo-amount]` short code to show the current amount of funds collected for the campaign.

The short code takes the following parameters:

 * `name` (**Required**) - Specify the name of the campaign for which to display the current amount. Can also be the numeric ID of the campaign as shown in the campaign list. If this is not specified or an invalid value is specified, the short code will show an error message instead of the current amount.

#### Current Campaign Funding Percentage

Use the `[kickgogo-percentage]` short code to show the current percentage of funds collected of the goal total for the campaign.

The short code takes the following parameters:

 * `name` (**Required**) - Specify the name of the campaign for which to display the current percentage. Can also be the numeric ID of the campaign as shown in the campaign list. If this is not specified or an invalid value is specified, the short code will show an error message instead of the current percentag.

Note that if the campaign funding passes the goal, the value shown will be higher than 100%.

