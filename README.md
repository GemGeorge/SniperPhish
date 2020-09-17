# SniperPhish
[SniperPhish](https://sniperphish.com/) is a phishing toolkit for pentester or security professionals to enhance user awareness by simulating real-world phishing attacks. SniperPhish helps to combine both phishing emails and phishing websites you created to centrally track user actions. The tool is designed in a view of performing professional phishing exercise and would be reminded to take prior permission from the targeted organization to avoid legal implications.

![PHP](https://img.shields.io/static/v1?label=php&message=>=7.3&color=green&style=flat&logo=php)
![Platform](https://img.shields.io/static/v1?label=Platform&message=Linux/Windows&color=orange&style=flat)
![License](https://img.shields.io/static/v1?label=License&message=MIT&color=blue&style=flat)
## Installation
1. Download the source code and put it in your web root folder
2. Open http://localhost/install in your browser and follow the steps for installation
3. After installation, open http://localhost/spear to login
>Default login - *Username: `admin`   Password: `sniperphish`*

## Main Features
* Web tracker code generation - track your website visits and form submissions independently
* Create and schedule Phishing mail campaigns
* Combine your phishing site with email campaign for centrally tracking
* An independent "Simple Tracker" module for quick tracking an email or web page visit
* Advance report generation - generate reports based on the tracking data you needed
* Custom tracker images and dynamic QR codes in messages
* Track phishing message replies

## Screenshots
<kbd>![SniperPhish](https://user-images.githubusercontent.com/15928266/91721076-bcb32680-ebca-11ea-9810-a13c24fe12f7.png)</kbd>

<kbd>![Web-Mail_campaign_dashboard](https://user-images.githubusercontent.com/15928266/91721067-bb81f980-ebca-11ea-8b50-4dbaad13510a.png)</kbd>

## Creating Web-Email Campaign
We create web tracker -> Add the web tracker to the phishing website -> create mail campaign with a link pointing to the phishing website -> start mail campaign.
#### Creating a web tracker:
1. Design your website in your favorite programming language. Make sure you provided unique "id" and "name" value for HTML fields such as text field, checkbox etc.
2. Generate web-tracker code `Web Tracker -> New Tracker`. The "Web Pages" tab list the pages you want to track
    * To track form submission data, provide the "id" or "name" values of HTML fields present in your phishing site form.
    * Repeat above for each page in your phishing site.
3. From the final output, copy the generated JavaScript link and add it under the <head> section of each website page. 
4. Finally, save the tracker created. Now the tracker is activated and listening in the background. Opening your phishing site or data submission is tracked.

#### Creating an Email campaign:
1. Go to `Email Campaign -> User Group` and add target users 
2. Go to `Email Campaign -> Sender List` and configure Mail server details
3. Go to `Email Campaign -> Email Template` and create mail template. When you add your phishing website link, make sure to append `?cid={{CID}}` at the end. This is to distinguish each users. For example, `http://yourphishingsite.com/login?cid={{CID}}`
4. Now go to `Email Campaign -> Campaign List -> New Mail Campaign` and select/fill the fields to create campaign.
5. Start Mail campaign

#### Viewing combined Web-Email Result
Open `Web-MailCamp Dashboard -> Select Campaign` and select Mail Campaign and Web Tracker you created.
<kbd><img src="https://user-images.githubusercontent.com/15928266/91721829-f8022500-ebcb-11ea-9acf-b82cbd37f174.png" height="286"></img></kbd>

## More
* SniperPhish website: https://sniperphish.com/
* SniperPhish demo: https://demo.sniperphish.com/spear/

## SniperPhish honors contributions of
Joseph Nygil ([@j_nygil](https://twitter.com/j_nygil)) and Sreehari Haridas ([@sr33h4ri](https://twitter.com/sr33h4ri))

## Donation
If this project help you 'Phish', you can give me a cup of coffee :) 

[![paypal](https://www.paypalobjects.com/en_GB/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=KMCVZ4DK4BEEW&lc=IN&item_name=SniperPhish%20Donation&button_subtype=services&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=USD&bn=PP%2dBuyNowBF%3abtn_buynowCC_LG%2egif%3aNonHosted)
[![bitcoin](https://user-images.githubusercontent.com/15928266/88777414-c3104600-d1b9-11ea-9262-10bae6805779.png)](https://sniperphish.com/donate)
