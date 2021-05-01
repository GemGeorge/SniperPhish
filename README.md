# SniperPhish
[SniperPhish](https://sniperphish.com/) is a phishing toolkit for pentester or security professionals to enhance user awareness by simulating real-world phishing attacks. SniperPhish helps to combine both phishing emails and phishing websites you created to centrally track user actions. The tool is designed in a view of performing professional phishing exercise and would be reminded to take prior permission from the targeted organization to avoid legal implications.

![PHP](https://img.shields.io/static/v1?label=php&message=>=7.3&color=green&style=flat&logo=php)
![Platform](https://img.shields.io/static/v1?label=Platform&message=Linux/Windows&color=orange&style=flat)
![License](https://img.shields.io/static/v1?label=License&message=MIT&color=blue&style=flat)
## Installation
1. Clone the repo or download the latest release
2. Put the contents in your web root folder
3. Open installation page http://localhost/install in your browser and follow the steps
4. After installation, SniperPhish will redirect to login page http://localhost/spear
>Default login - *Username: `admin`   Password: `sniperphish`*

## Main Features
* Web tracker code generation - track your website visits and form submissions independently
* Tracks data from phishing website containg any number of pages
* Create and schedule Phishing mail campaigns
* Combine your phishing site with email campaign for centrally tracking
* An independent "Quick Tracker" module for quick tracking an email or web page visit
* Advance report generation - generate reports based on the tracking data you needed
* Mail campaigns with QR/Bar code support (both locally and remotely embedding in mails)
* Track phishing message replies
* Signed and encrypted mail support
* Advanced mail campaign customization – read receipt, TO/CC/BCC emails etc.
* Anti-flood control for emails
* Non-ASCII (Punycode transcription) support for email and domain

## Screenshots
<kbd>![Web-Email Campaign Dashboard](https://user-images.githubusercontent.com/15928266/116777794-e9447880-aaa0-11eb-9697-af5f5617b279.PNG)</kbd>

<kbd>![Web Tracker Insertion](https://user-images.githubusercontent.com/15928266/116777832-198c1700-aaa1-11eb-9f10-4a0b27c172d8.gif)</kbd>

## Creating Web-Email Campaign - Quick Guide
In short, we create web tracker -> Add the web tracker to the phishing website -> create mail campaign with a link pointing to the phishing website -> start mail campaign.
#### Creating a web tracker:
1. Design your website in your favorite programming language. Make sure you provided unique "id" and "name" value for HTML fields such as text field, checkbox etc.
2. Generate a web-tracker code `Web Tracker -> New Tracker` for your phishing site. The "Web Pages" tab lists the pages you want to track.
    * To track form submission data, provide the "id" or "name" values of HTML fields present in your phishing site form.
    * Repeat above for each page in your phishing site which is required to track.
3. From the final output, copy the generated JS link and place it in between &lt;Head&gt; and &lt;/Head&gt; section of each website page. This JS script contains the tracking code.
4. Finally, save the tracker created. Now the tracker is activated and listening in the background. Opening your phishing site pages or form submissions are tracked.

#### Creating an Email campaign:
1. Go to `Email Campaign -> User Group` and add target users 
2. Go to `Email Campaign -> Sender List` and configure Mail server details
3. Go to `Email Campaign -> Email Template` and create mail template. Here, you can to link your phishing website based on the web tracker you created. For that, click on `Insert` menu from email template editor and chose `Link to Web Tracker`. Select your web tracker from the pop-up window and insert it.
4. Now go to `Email Campaign -> Campaign List -> New Mail Campaign` and select/fill the fields to create the campaign.
5. Start Mail campaign

_Note: SniperPhish tracks your phishing website only if the page is called by appending `cid` parameter (ie. `?cid={{CID}}`) at the end. For example opening `http://yourphishingsite.com/login?cid=abcd` will be tracked, but not `http://yourphishingsite.com/login`. Above 3rd step does this by default._

#### Viewing combined Web-Email Result
Go to `Web-MailCamp Dashboard -> Select Campaign`. Then selct the web tracker and email campaign you created.<br/>
<kbd><img src="https://user-images.githubusercontent.com/15928266/116777253-2e1bdf80-aaa0-11eb-9c44-e1db8f200c39.png" height="286"></img></kbd>

## More
* SniperPhish website: https://sniperphish.com/
* SniperPhish demo: https://demo.sniperphish.com/spear/

## SniperPhish honors contributions of
Joseph Nygil ([@j_nygil](https://twitter.com/j_nygil)) and Sreehari Haridas ([@sr33h4ri](https://twitter.com/sr33h4ri))

## Donation
If this project help you 'Phish', you can give me a cup of coffee :) 

[![bitcoin](https://user-images.githubusercontent.com/15928266/88777414-c3104600-d1b9-11ea-9262-10bae6805779.png)](https://sniperphish.com/donate)
