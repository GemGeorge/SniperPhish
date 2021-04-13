<?php

/**
 * Browser detection class file.
 * This file contains everything required to use the BrowserDetection class. Tested with PHP 5.3.29 - 7.2.4.
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any
 * later version (if any).
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details at: https://www.gnu.org/licenses/lgpl-3.0.html
 *
 * @package Browser_Detection
 * @version 2.9.5
 * @last-modified February 2, 2020
 * @author Alexandre Valiquette
 * @copyright Copyright (c) 2020, Wolfcast
 * @link https://wolfcast.com/
 */


namespace Wolfcast;


/**
 * The BrowserDetection class facilitates the identification of the user's environment such as Web browser, version,
 * platform and device type.
 *
 * Typical usage:
 *
 * $browser = new Wolfcast\BrowserDetection();
 * if ($browser->getName() == Wolfcast\BrowserDetection::BROWSER_FIREFOX &&
 *     $browser->compareVersions($browser->getVersion(), '5.0') >= 0) {
 *     echo 'You are using FireFox version 5 or greater.';
 * }
 *
 * The class is a rewrite of Chris Schuld's Browser class version 1.9 which is mostly unmaintained since August 20th,
 * 2010. Chris' class was based on the original work from Gary White.
 *
 * Updates:
 *
 * 2020-02-02: Version 2.9.5
 *  + WARNING! Breaking change: complete rework of robots detection. Now robot name and version is detected in addition
 *    of browser name and version. Use getRobotName() and getRobotVersion() when isRobot() is true.
 *  + WARNING! Breaking change: due to robots detection rework the following methods signatures has changed (isRobot
 *    parameter removed): addCustomBrowserDetection(), checkSimpleBrowserUA(), checkBrowserUAWithVersion().
 *  + Added possibility to support new robots with addCustomRobotDetection().
 *  + Added support for the new Microsoft Edge based on Chromium.
 *  + Added version names for Android 10 and later (Google no longer use candy names for new versions).
 *  + Added macOS Catalina detection.
 *  + Added Windows Server 2019 detection (Windows Server 2016 can be no longer detected due to the fact that they both
 *    use the same version number and that the build is not included in the user agent).
 *
 * 2019-03-27: Version 2.9.3
 *  + Fixed Edge detection on Android.
 *  + Added Android Q detection.
 *  + Now filtering superglobals.
 *
 * 2019-02-28: Version 2.9.2
 *  + Fixed Opera detection.
 *
 * 2018-08-23: Version 2.9.1
 *  + Fixed Chrome detection under iOS.
 *  + Added Android Pie detection.
 *  + Added macOS Mojave detection.
 *
 * 2018-07-15: Version 2.9.0
 *  + WARNING! Breaking change: new Wolfcast namespace. Use new Wolfcast\BrowserDetection().
 *  + iPad, iPhone and iPod are all under iOS now.
 *  + Added Android Oreo detection.
 *  + Added macOS High Sierra detection.
 *  + Added UC Browser detection.
 *  + Improved regular expressions (even less false positives).
 *  + Removed AOL detection.
 *  + Removed the following Web browsers detection: Amaya, Galeon, NetPositive, OmniWeb, Vivaldi detection (use
 *    addCustomBrowserDetection()).
 *  + Removed the following legacy platforms detection: BeOS, OS/2, SunOS (use addCustomPlatformDetection()).
 *
 * 2016-11-28: Version 2.5.1
 *  + Better detection of 64-bit platforms.
 *
 * 2016-08-19: Version 2.5.0
 *  + Platform version and platform version name are now supported for Mac.
 *  + Fixed platform version name for Android.
 *
 * 2016-08-02: Version 2.4.0
 *  + Platform version and platform version name are now supported for Android.
 *  + Added support for the Samsung Internet browser.
 *  + Added support for the Vivaldi browser.
 *  + Better support for legacy Windows versions.
 *
 * 2016-02-11: Version 2.3.0
 *  + WARNING! Breaking change: public method getBrowser() is renamed to getName().
 *  + WARNING! Breaking change: changed the compareVersions() return values to be more in line with other libraries.
 *  + You can now get the exact platform version (name or version numbers) on which the browser is run on with
 *    getPlatformVersion(). Only working with Windows operating systems at the moment.
 *  + You can now determine if the browser is executed from a 64-bit platform with is64bitPlatform().
 *  + Better detection of mobile platform for Googlebot.
 *
 * 2016-01-04: Version 2.2.0
 *  + Added support for Microsoft Edge.
 *
 * 2014-12-30: Version 2.1.2
 *  + Better detection of Opera.
 *
 * 2014-07-11: Version 2.1.1
 *  + Better detection of mobile devices and platforms.
 *
 * 2014-06-04: Version 2.1.0
 *  + Added support for IE 11+.
 *
 * 2013-05-27: Version 2.0.0 which is (almost) a complete rewrite based on Chris Schuld's Browser class version 1.9 plus
 * changes below.
 *  + Added support for Opera Mobile
 *  + Added support for the Windows Phone (formerly Windows Mobile) platform
 *  + Added support for BlackBerry Tablet OS and BlackBerry 10
 *  + Added support for the Symbian platform
 *  + Added support for Bingbot
 *  + Added support for the Yahoo! Multimedia crawler
 *  + Removed iPhone/iPad/iPod browsers since there are not browsers but platforms - test them with getPlatform()
 *  + Removed support for Shiretoko (Firefox 3.5 alpha/beta) and MSN Browser
 *  + Merged Nokia and Nokia S60
 *  + Updated some deprecated browser names
 *  + Many public methods are now protected
 *  + Documentation updated
 *
 * 2010-07-04:
 *  + Added detection of IE compatibility view - test with getIECompatibilityView()
 *  + Added support for all (deprecated) Netscape versions
 *  + Added support for Safari < 3.0
 *  + Better Firefox version parsing
 *  + Better Opera version parsing
 *  + Better Mozilla detection
 *
 * @package Browser_Detection
 * @version 2.9.5
 * @last-modified February 2, 2020
 * @author Alexandre Valiquette, Chris Schuld, Gary White
 * @copyright Copyright (c) 2020, Wolfcast
 * @license https://www.gnu.org/licenses/lgpl-3.0.html
 * @link https://wolfcast.com/
 * @link https://wolfcast.com/open-source/browser-detection/tutorial.php
 * @link https://chrisschuld.com/
 * @link https://www.apptools.com/phptools/browser/
 */
class BrowserDetection
{

    /**#@+
     * Constant for the name of the Web browser.
     */
    const BROWSER_ANDROID = 'Android';
    const BROWSER_BLACKBERRY = 'BlackBerry';
    const BROWSER_CHROME = 'Chrome';
    const BROWSER_EDGE = 'Edge';
    const BROWSER_FIREBIRD = 'Firebird';
    const BROWSER_FIREFOX = 'Firefox';
    const BROWSER_ICAB = 'iCab';
    const BROWSER_ICECAT = 'GNU IceCat';
    const BROWSER_ICEWEASEL = 'GNU IceWeasel';
    const BROWSER_IE = 'Internet Explorer';
    const BROWSER_IE_MOBILE = 'Internet Explorer Mobile';
    const BROWSER_KONQUEROR = 'Konqueror';
    const BROWSER_LYNX = 'Lynx';
    const BROWSER_MOZILLA = 'Mozilla';
    const BROWSER_MSNTV = 'MSN TV';
    const BROWSER_NETSCAPE = 'Netscape';
    const BROWSER_NOKIA = 'Nokia Browser';
    const BROWSER_OPERA = 'Opera';
    const BROWSER_OPERA_MINI = 'Opera Mini';
    const BROWSER_OPERA_MOBILE = 'Opera Mobile';
    const BROWSER_PHOENIX = 'Phoenix';
    const BROWSER_SAFARI = 'Safari';
    const BROWSER_SAMSUNG = 'Samsung Internet';
    const BROWSER_TABLET_OS = 'BlackBerry Tablet OS';
    const BROWSER_UC = 'UC Browser';
    const BROWSER_UNKNOWN = 'unknown';
    /**#@-*/

    /**#@+
     * Constant for the name of the platform on which the Web browser runs.
     */
    const PLATFORM_ANDROID = 'Android';
    const PLATFORM_BLACKBERRY = 'BlackBerry';
    const PLATFORM_FREEBSD = 'FreeBSD';
    const PLATFORM_IOS = 'iOS';
    const PLATFORM_LINUX = 'Linux';
    const PLATFORM_MACINTOSH = 'Macintosh';
    const PLATFORM_NETBSD = 'NetBSD';
    const PLATFORM_NOKIA = 'Nokia';
    const PLATFORM_OPENBSD = 'OpenBSD';
    const PLATFORM_OPENSOLARIS = 'OpenSolaris';
    const PLATFORM_SYMBIAN = 'Symbian';
    const PLATFORM_UNKNOWN = 'unknown';
    const PLATFORM_VERSION_UNKNOWN = 'unknown';
    const PLATFORM_WINDOWS = 'Windows';
    const PLATFORM_WINDOWS_CE = 'Windows CE';
    const PLATFORM_WINDOWS_PHONE = 'Windows Phone';
    /**#@-*/

    /**#@+
     * Constant for the name of the robot.
     */
    const ROBOT_BINGBOT = 'Bingbot';
    const ROBOT_GOOGLEBOT = 'Googlebot';
    const ROBOT_MSNBOT = 'MSNBot';
    const ROBOT_SLURP = 'Yahoo! Slurp';
    const ROBOT_UNKNOWN = '';
    const ROBOT_VERSION_UNKNOWN = '';
    const ROBOT_W3CVALIDATOR = 'W3C Validator';
    const ROBOT_YAHOO_MM = 'Yahoo! Multimedia';
    /**#@-*/

    /**
     * Version unknown constant.
     */
    const VERSION_UNKNOWN = 'unknown';


    /**
     * @var string
     * @access private
     */
    private $_agent = '';

    /**
     * @var string
     * @access private
     */
    private $_browserName = '';

    /**
     * @var string
     * @access private
     */
    private $_compatibilityViewName = '';

    /**
     * @var string
     * @access private
     */
    private $_compatibilityViewVer = '';

    /**
     * @var array
     * @access private
     */
    private $_customBrowserDetection = array();

    /**
     * @var array
     * @access private
     */
    private $_customPlatformDetection = array();

    /**
     * @var array
     * @access private
     */
    private $_customRobotDetection = array();

    /**
     * @var boolean
     * @access private
     */
    private $_is64bit = false;

    /**
     * @var boolean
     * @access private
     */
    private $_isMobile = false;

    /**
     * @var boolean
     * @access private
     */
    private $_isRobot = false;

    /**
     * @var string
     * @access private
     */
    private $_platform = '';

    /**
     * @var string
     * @access private
     */
    private $_platformVersion = '';

    /**
     * @var string
     * @access private
     */
    private $_robotName = '';

    /**
     * @var string
     * @access private
     */
    private $_robotVersion = '';

    /**
     * @var string
     * @access private
     */
    private $_version = '';


    //--- MAGIC METHODS ------------------------------------------------------------------------------------------------


    /**
     * BrowserDetection class constructor.
     * @param string $useragent (optional) The user agent to work with. Leave empty for the current user agent
     * (contained in $_SERVER['HTTP_USER_AGENT']).
     */
    public function __construct($useragent = '')
    {
        $this->setUserAgent($useragent);
    }

    /**
     * Determine how the class will react when it is treated like a string.
     * @return string Returns an HTML formatted string with a summary of the browser informations.
     */
    public function __toString()
    {
        $result = '';

        $values = array();
        $values[] = array('label' => 'User agent', 'value' => $this->getUserAgent());
        $values[] = array('label' => 'Browser name', 'value' => $this->getName());
        $values[] = array('label' => 'Browser version', 'value' => $this->getVersion());
        $values[] = array('label' => 'Platform family', 'value' => $this->getPlatform());
        $values[] = array('label' => 'Platform version', 'value' => $this->getPlatformVersion(true));
        $values[] = array('label' => 'Platform version name', 'value' => $this->getPlatformVersion());
        $values[] = array('label' => 'Platform is 64-bit', 'value' => $this->is64bitPlatform() ? 'true' : 'false');
        $values[] = array('label' => 'Is mobile', 'value' => $this->isMobile() ? 'true' : 'false');
        $values[] = array('label' => 'Is robot', 'value' => $this->isRobot() ? 'true' : 'false');
        $values[] = array('label' => 'Robot name', 'value' => $this->isRobot() ? ($this->getRobotName() != self::ROBOT_UNKNOWN ? $this->getRobotName() : 'Unknown') : 'Not applicable');
        $values[] = array('label' => 'Robot version', 'value' => $this->isRobot() ? ($this->getRobotVersion() != self::ROBOT_VERSION_UNKNOWN ? $this->getRobotVersion() : 'Unknown') : 'Not applicable');
        $values[] = array('label' => 'IE is in compatibility view', 'value' => $this->isInIECompatibilityView() ? 'true' : 'false');
        $values[] = array('label' => 'Emulated IE version', 'value' => $this->isInIECompatibilityView() ? $this->getIECompatibilityView() : 'Not applicable');
        $values[] = array('label' => 'Is Chrome Frame', 'value' => $this->isChromeFrame() ? 'true' : 'false');

        foreach ($values as $currVal) {
            $result .= '<strong>' . htmlspecialchars($currVal['label'], ENT_NOQUOTES) . ':</strong> ' . $currVal['value'] . '<br />' . PHP_EOL;
        }

        return $result;
    }


    //--- PUBLIC MEMBERS -----------------------------------------------------------------------------------------------


    /**
     * Dynamically add support for a new Web browser.
     * @param string $browserName The Web browser name (used for display).
     * @param mixed $uaNameToLookFor (optional) The string (or array of strings) representing the browser name to find
     * in the user agent. If omitted, $browserName will be used.
     * @param boolean $isMobile (optional) Determines if the browser is from a mobile device.
     * @param string $separator (optional) The separator string used to split the browser name and the version number in
     * the user agent.
     * @param boolean $uaNameFindWords (optional) Determines if the browser name to find should match a word instead of
     * a part of a word. For example "Bar" would not be found in "FooBar" when true but would be found in "Foo Bar".
     * When set to false, the browser name can be found anywhere in the user agent string.
     * @see removeCustomBrowserDetection()
     * @return boolean Returns true if the custom rule has been added, false otherwise.
     */
    public function addCustomBrowserDetection($browserName, $uaNameToLookFor = '', $isMobile = false, $separator = '/', $uaNameFindWords = true)
    {
        if ($browserName == '') {
            return false;
        }
        if (array_key_exists($browserName, $this->_customBrowserDetection)) {
            unset($this->_customBrowserDetection[$browserName]);
        }
        if ($uaNameToLookFor == '') {
            $uaNameToLookFor = $browserName;
        }
        $this->_customBrowserDetection[$browserName] = array('uaNameToLookFor' => $uaNameToLookFor, 'isMobile' => $isMobile == true,
                                                             'separator' => $separator, 'uaNameFindWords' => $uaNameFindWords == true);
        return true;
    }

    /**
     * Dynamically add support for a new platform.
     * @param string $platformName The platform name (used for display).
     * @param mixed $platformNameToLookFor (optional) The string (or array of strings) representing the platform name to
     * find in the user agent. If omitted, $platformName will be used.
     * @param boolean $isMobile (optional) Determines if the platform is from a mobile device.
     * @param boolean $uaNameFindWords (optional) Determines if the platform name to find should match a word instead of
     * a part of a word. For example "Bar" would not be found in "FooBar" when true but would be found in "Foo Bar".
     * @see removeCustomPlatformDetection()
     * @return boolean Returns true if the custom rule has been added, false otherwise.
     */
    public function addCustomPlatformDetection($platformName, $platformNameToLookFor = '', $isMobile = false, $uaNameFindWords = true)
    {
        if ($platformName == '') {
            return false;
        }
        if (array_key_exists($platformName, $this->_customPlatformDetection)) {
            unset($this->_customPlatformDetection[$platformName]);
        }
        if ($platformNameToLookFor == '') {
            $platformNameToLookFor = $platformName;
        }
        $this->_customPlatformDetection[$platformName] = array('platformNameToLookFor' => $platformNameToLookFor,
                                                               'isMobile' => $isMobile == true,
                                                               'uaNameFindWords' => $uaNameFindWords == true);
        return true;
    }

    /**
     * Dynamically add support for a new robot.
     * @param string $robotName The robot name (used for display).
     * @param mixed $uaNameToLookFor (optional) The string (or array of strings) representing the robot name to find
     * in the user agent. If omitted, $robotName will be used.
     * @param boolean $isMobile (optional) Determines if the robot should be considered as mobile or not.
     * @param string $separator (optional) The separator string used to split the robot name and the version number in
     * the user agent.
     * @param boolean $uaNameFindWords (optional) Determines if the robot name to find should match a word instead of
     * a part of a word. For example "Bar" would not be found in "FooBar" when true but would be found in "Foo Bar".
     * When set to false, the robot name can be found anywhere in the user agent string.
     * @see removeCustomRobotDetection()
     * @return boolean Returns true if the custom rule has been added, false otherwise.
     */
    public function addCustomRobotDetection($robotName, $uaNameToLookFor = '', $isMobile = false, $separator = '/', $uaNameFindWords = true)
    {
        if ($robotName == '') {
            return false;
        }
        if (array_key_exists($robotName, $this->_customRobotDetection)) {
            unset($this->_customRobotDetection[$robotName]);
        }
        if ($uaNameToLookFor == '') {
            $uaNameToLookFor = $robotName;
        }
        $this->_customRobotDetection[$robotName] = array('uaNameToLookFor' => $uaNameToLookFor, 'isMobile' => $isMobile == true,
                                                         'separator' => $separator, 'uaNameFindWords' => $uaNameFindWords == true);
        return true;
    }

    /**
     * Compare two version number strings.
     * @param string $sourceVer The source version number.
     * @param string $compareVer The version number to compare with the source version number.
     * @return int Returns -1 if $sourceVer < $compareVer, 0 if $sourceVer == $compareVer or 1 if $sourceVer >
     * $compareVer.
     */
    public function compareVersions($sourceVer, $compareVer)
    {
        $sourceVer = explode('.', $sourceVer);
        foreach ($sourceVer as $k => $v) {
            $sourceVer[$k] = $this->parseInt($v);
        }

        $compareVer = explode('.', $compareVer);
        foreach ($compareVer as $k => $v) {
            $compareVer[$k] = $this->parseInt($v);
        }

        if (count($sourceVer) != count($compareVer)) {
            if (count($sourceVer) > count($compareVer)) {
                for ($i = count($compareVer); $i < count($sourceVer); $i++) {
                    $compareVer[$i] = 0;
                }
            } else {
                for ($i = count($sourceVer); $i < count($compareVer); $i++) {
                    $sourceVer[$i] = 0;
                }
            }
        }

        foreach ($sourceVer as $i => $srcVerPart) {
            if ($srcVerPart > $compareVer[$i]) {
                return 1;
            } else {
                if ($srcVerPart < $compareVer[$i]) {
                    return -1;
                }
            }
        }

        return 0;
    }

    /**
     * Get the name and version of the browser emulated in the compatibility view mode (if any). Since Internet
     * Explorer 8, IE can be put in compatibility mode to make websites that were created for older browsers, especially
     * IE 6 and 7, look better in IE 8+ which renders web pages closer to the standards and thus differently from those
     * older versions of IE.
     * @param boolean $asArray (optional) Determines if the return value must be an array (true) or a string (false).
     * @return mixed If a string was requested, the function returns the name and version of the browser emulated in
     * the compatibility view mode or an empty string if the browser is not in compatibility view mode. If an array was
     * requested, an array with the keys 'browser' and 'version' is returned.
     */
    public function getIECompatibilityView($asArray = false)
    {
        if ($asArray) {
            return array('browser' => $this->_compatibilityViewName, 'version' => $this->_compatibilityViewVer);
        } else {
            return trim($this->_compatibilityViewName . ' ' . $this->_compatibilityViewVer);
        }
    }

    /**
     * Return the BrowserDetection class version.
     * @return string Returns the version as a sting with the #.#.# format.
     */
    public function getLibVersion()
    {
        return '2.9.5';
    }

    /**
     * Get the name of the browser. All of the return values are class constants. You can compare them like this:
     * $myBrowserInstance->getName() == BrowserDetection::BROWSER_FIREFOX.
     * @return string Returns the name of the browser or BrowserDetection::BROWSER_UNKNOWN if unknown.
     */
    public function getName()
    {
        return $this->_browserName;
    }

    /**
     * Get the name of the platform family on which the browser is run on (such as Windows, Apple, etc.). All of
     * the return values are class constants. You can compare them like this:
     * $myBrowserInstance->getPlatform() == BrowserDetection::PLATFORM_ANDROID.
     * @return string Returns the name of the platform or BrowserDetection::PLATFORM_UNKNOWN if unknown.
     */
    public function getPlatform()
    {
        return $this->_platform;
    }

    /**
     * Get the platform version on which the browser is run on. It can be returned as a string number like 'NT 6.3' or
     * as a name like 'Windows 8.1'. When returning version string numbers for Windows NT OS families the number is
     * prefixed by 'NT ' to differentiate from older Windows 3.x & 9x release. At the moment only the Windows and
     * Android operating systems are supported.
     * @param boolean $returnVersionNumbers (optional) Determines if the return value must be versions numbers as a
     * string (true) or the version name (false).
     * @param boolean $returnServerFlavor (optional) Since some Windows NT versions have the same values, this flag
     * determines if the Server flavor is returned or not. For instance Windows 8.1 and Windows Server 2012 R2 both use
     * version 6.3. This parameter is only useful when testing for Windows.
     * @return string Returns the version name/version numbers of the platform or the constant PLATFORM_VERSION_UNKNOWN
     * if unknown.
     */
    public function getPlatformVersion($returnVersionNumbers = false, $returnServerFlavor = false)
    {
        if ($this->_platformVersion == self::PLATFORM_VERSION_UNKNOWN || $this->_platformVersion == '') {
            return self::PLATFORM_VERSION_UNKNOWN;
        }

        if ($returnVersionNumbers) {
            return $this->_platformVersion;
        } else {
            switch ($this->getPlatform()) {
                case self::PLATFORM_WINDOWS:
                    if (substr($this->_platformVersion, 0, 3) == 'NT ') {
                        return $this->windowsNTVerToStr(substr($this->_platformVersion, 3), $returnServerFlavor);
                    } else {
                        return $this->windowsVerToStr($this->_platformVersion);
                    }
                    break;

                case self::PLATFORM_MACINTOSH:
                    return $this->macVerToStr($this->_platformVersion);

                case self::PLATFORM_ANDROID:
                    return $this->androidVerToStr($this->_platformVersion);

                case self::PLATFORM_IOS:
                    return $this->iOSVerToStr($this->_platformVersion);

                default: return self::PLATFORM_VERSION_UNKNOWN;
            }
        }
    }

    /**
     * Get the name of the robot. All of the return values are class constants. You can compare them like this:
     * $myBrowserInstance->getRobotName() == BrowserDetection::ROBOT_GOOGLEBOT.
     * @return string Returns the name of the robot or BrowserDetection::ROBOT_UNKNOWN if unknown.
     */
    public function getRobotName()
    {
        return $this->_robotName;
    }

    /**
     * Get the version of the robot.
     * @return string Returns the version of the robot or BrowserDetection::ROBOT_VERSION_UNKNOWN if unknown.
     */
    public function getRobotVersion()
    {
        return $this->_robotVersion;
    }

    /**
     * Get the user agent value used by the class to determine the browser details.
     * @return string The user agent string.
     */
    public function getUserAgent()
    {
        return $this->_agent;
    }

    /**
     * Get the version of the browser.
     * @return string Returns the version of the browser or BrowserDetection::VERSION_UNKNOWN if unknown.
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Determine if the browser is executed from a 64-bit platform. Keep in mind that not all platforms/browsers report
     * this and the result may not always be accurate.
     * @return boolean Returns true if the browser is executed from a 64-bit platform.
     */
    public function is64bitPlatform()
    {
        return $this->_is64bit;
    }

    /**
     * Determine if the browser runs Google Chrome Frame (it's a plug-in designed for Internet Explorer 6+ based on the
     * open-source Chromium project - it's like a Chrome browser within IE).
     * @return boolean Returns true if the browser is using Google Chrome Frame, false otherwise.
     */
    public function isChromeFrame()
    {
        return $this->containString($this->_agent, 'chromeframe');
    }

    /**
     * Determine if the browser is in compatibility view or not. Since Internet Explorer 8, IE can be put in
     * compatibility mode to make websites that were created for older browsers, especially IE 6 and 7, look better in
     * IE 8+ which renders web pages closer to the standards and thus differently from those older versions of IE.
     * @return boolean Returns true if the browser is in compatibility view, false otherwise.
     */
    public function isInIECompatibilityView()
    {
        return ($this->_compatibilityViewName != '') || ($this->_compatibilityViewVer != '');
    }

    /**
     * Determine if the browser is from a mobile device or not.
     * @return boolean Returns true if the browser is from a mobile device, false otherwise.
     */
    public function isMobile()
    {
        return $this->_isMobile;
    }

    /**
     * Determine if the browser is a robot (Googlebot, Bingbot, Yahoo! Slurp...) or not.
     * @return boolean Returns true if the browser is a robot, false otherwise.
     */
    public function isRobot()
    {
        return $this->_isRobot;
    }

    /**
     * Remove support for a previously added Web browser.
     * @param string $browserName The Web browser name as used when added.
     * @see addCustomBrowserDetection()
     * @return boolean Returns true if the custom rule has been found and removed, false otherwise.
     */
    public function removeCustomBrowserDetection($browserName)
    {
        if (array_key_exists($browserName, $this->_customBrowserDetection)) {
            unset($this->_customBrowserDetection[$browserName]);
            return true;
        }

        return false;
    }

    /**
     * Remove support for a previously added platform.
     * @param string $platformName The platform name as used when added.
     * @see addCustomPlatformDetection()
     * @return boolean Returns true if the custom rule has been found and removed, false otherwise.
     */
    public function removeCustomPlatformDetection($platformName)
    {
        if (array_key_exists($platformName, $this->_customPlatformDetection)) {
            unset($this->_customPlatformDetection[$platformName]);
            return true;
        }

        return false;
    }

    /**
     * Remove support for a previously added robot.
     * @param string $robotName The robot name as used when added.
     * @see addCustomRobotDetection()
     * @return boolean Returns true if the custom rule has been found and removed, false otherwise.
     */
    public function removeCustomRobotDetection($robotName)
    {
        if (array_key_exists($robotName, $this->_customRobotDetection)) {
            unset($this->_customRobotDetection[$robotName]);
            return true;
        }

        return false;
    }

    /**
     * Set the user agent to use with the class.
     * @param string $agentString (optional) The value of the user agent. If an empty string is sent (default),
     * $_SERVER['HTTP_USER_AGENT'] will be used.
     */
    public function setUserAgent($agentString = '')
    {
        if (!is_string($agentString) || trim($agentString) == '') {
            //https://bugs.php.net/bug.php?id=49184
            if (filter_has_var(INPUT_SERVER, 'HTTP_USER_AGENT')) {
                $agentString = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
            } else if (array_key_exists('HTTP_USER_AGENT', $_SERVER) && is_string($_SERVER['HTTP_USER_AGENT'])) {
                $agentString = filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
            } else {
                $agentString = '';
            }

            if ($agentString === false || $agentString === NULL) {
                //filter_input or filter_var failed
                $agentString = '';
            }
        }

        $this->reset();
        $this->_agent = $agentString;
        $this->detect();
    }


    //--- PROTECTED MEMBERS --------------------------------------------------------------------------------------------


    /**
     * Convert the Android version numbers to the operating system name. For instance '1.6' returns 'Donut'.
     * @access protected
     * @param string $androidVer The Android version numbers as a string.
     * @return string The operating system name or the constant PLATFORM_VERSION_UNKNOWN if nothing match the version
     * numbers.
     */
    protected function androidVerToStr($androidVer)
    {
        //https://en.wikipedia.org/wiki/Android_version_history

        if ($this->compareVersions($androidVer, '10') >= 0) {
            $majorVer = strstr($androidVer, '.', true);
            if ($majorVer == '') {
                $majorVer = $androidVer;
            }
            return self::BROWSER_ANDROID . ' ' . $majorVer;
        } else if ($this->compareVersions($androidVer, '9') >= 0 && $this->compareVersions($androidVer, '10') < 0) {
            return 'Pie';
        } else if ($this->compareVersions($androidVer, '8') >= 0 && $this->compareVersions($androidVer, '9') < 0) {
            return 'Oreo';
        } else if ($this->compareVersions($androidVer, '7') >= 0 && $this->compareVersions($androidVer, '8') < 0) {
            return 'Nougat';
        } else if ($this->compareVersions($androidVer, '6') >= 0 && $this->compareVersions($androidVer, '7') < 0) {
            return 'Marshmallow';
        } else if ($this->compareVersions($androidVer, '5') >= 0 && $this->compareVersions($androidVer, '5.2') < 0) {
            return 'Lollipop';
        } else if ($this->compareVersions($androidVer, '4.4') >= 0 && $this->compareVersions($androidVer, '4.5') < 0) {
            return 'KitKat';
        } else if ($this->compareVersions($androidVer, '4.1') >= 0 && $this->compareVersions($androidVer, '4.4') < 0) {
            return 'Jelly Bean';
        } else if ($this->compareVersions($androidVer, '4') >= 0 && $this->compareVersions($androidVer, '4.1') < 0) {
            return 'Ice Cream Sandwich';
        } else if ($this->compareVersions($androidVer, '3') >= 0 && $this->compareVersions($androidVer, '3.3') < 0) {
            return 'Honeycomb';
        } else if ($this->compareVersions($androidVer, '2.3') >= 0 && $this->compareVersions($androidVer, '2.4') < 0) {
            return 'Gingerbread';
        } else if ($this->compareVersions($androidVer, '2.2') >= 0 && $this->compareVersions($androidVer, '2.3') < 0) {
            return 'Froyo';
        } else if ($this->compareVersions($androidVer, '2') >= 0 && $this->compareVersions($androidVer, '2.2') < 0) {
            return 'Eclair';
        } else if ($this->compareVersions($androidVer, '1.6') == 0) {
            return 'Donut';
        } else if ($this->compareVersions($androidVer, '1.5') == 0) {
            return 'Cupcake';
        } else {
            return self::PLATFORM_VERSION_UNKNOWN; //Unknown/unnamed Android version
        }
    }

    /**
     * Determine if the browser is the Android browser (based on the WebKit layout engine and coupled with Chrome's
     * JavaScript engine) or not.
     * @access protected
     * @return boolean Returns true if the browser is the Android browser, false otherwise.
     */
    protected function checkBrowserAndroid()
    {
        //Android don't use the standard "Android/1.0", it uses "Android 1.0;" instead
        return $this->checkSimpleBrowserUA('Android', $this->_agent, self::BROWSER_ANDROID, true);
    }

    /**
     * Determine if the browser is the BlackBerry browser or not.
     * @access protected
     * @link https://web.archive.org/web/20170328000854/http://supportforums.blackberry.com/t5/Web-and-WebWorks-Development/How-to-detect-the-BlackBerry-Browser/ta-p/559862
     * @return boolean Returns true if the browser is the BlackBerry browser, false otherwise.
     */
    protected function checkBrowserBlackBerry()
    {
        $found = false;

        //Tablet OS check
        if ($this->checkSimpleBrowserUA('RIM Tablet OS', $this->_agent, self::BROWSER_TABLET_OS, true)) {
            return true;
        }

        //Version 6, 7 & 10 check (versions 8 & 9 does not exists)
        if ($this->checkBrowserUAWithVersion(array('BlackBerry', 'BB10'), $this->_agent, self::BROWSER_BLACKBERRY, true)) {
            if ($this->getVersion() == self::VERSION_UNKNOWN) {
                $found = true;
            } else {
                return true;
            }
        }

        //Version 4.2 to 5.0 check
        if ($this->checkSimpleBrowserUA('BlackBerry', $this->_agent, self::BROWSER_BLACKBERRY, true, '/', false)) {
            if ($this->getVersion() == self::VERSION_UNKNOWN) {
                $found = true;
            } else {
                return true;
            }
        }

        return $found;
    }

    /**
     * Determine if the browser is Chrome or not.
     * @access protected
     * @link https://www.google.com/chrome/
     * @return boolean Returns true if the browser is Chrome, false otherwise.
     */
    protected function checkBrowserChrome()
    {
        return $this->checkSimpleBrowserUA(array('Chrome', 'CriOS'), $this->_agent, self::BROWSER_CHROME);
    }

    /**
     * Determine if the browser is among the custom browser rules or not. Rules are checked in the order they were
     * added.
     * @access protected
     * @return boolean Returns true if we found the browser we were looking for in the custom rules, false otherwise.
     */
    protected function checkBrowserCustom()
    {
        foreach ($this->_customBrowserDetection as $browserName => $customBrowser) {
            $uaNameToLookFor = $customBrowser['uaNameToLookFor'];
            $isMobile = $customBrowser['isMobile'];
            $separator = $customBrowser['separator'];
            $uaNameFindWords = $customBrowser['uaNameFindWords'];
            if ($this->checkSimpleBrowserUA($uaNameToLookFor, $this->_agent, $browserName, $isMobile, $separator, $uaNameFindWords)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine if the browser is Edge or not.
     * @access protected
     * @return boolean Returns true if the browser is Edge, false otherwise.
     */
    protected function checkBrowserEdge()
    {
        return $this->checkSimpleBrowserUA(array('Edg', 'Edge', 'EdgA'), $this->_agent, self::BROWSER_EDGE);
    }

    /**
     * Determine if the browser is Firebird or not. Firebird was the name of Firefox from version 0.6 to 0.7.1.
     * @access protected
     * @return boolean Returns true if the browser is Firebird, false otherwise.
     */
    protected function checkBrowserFirebird()
    {
        return $this->checkSimpleBrowserUA('Firebird', $this->_agent, self::BROWSER_FIREBIRD);
    }

    /**
     * Determine if the browser is Firefox or not.
     * @access protected
     * @link https://www.mozilla.org/en-US/firefox/new/
     * @return boolean Returns true if the browser is Firefox, false otherwise.
     */
    protected function checkBrowserFirefox()
    {
        //Safari heavily matches with Firefox, ensure that Safari is filtered out...
        if (preg_match('/.*Firefox[ (\/]*([a-z0-9.-]*)/i', $this->_agent, $matches) &&
                !$this->containString($this->_agent, 'Safari')) {
            $this->setBrowser(self::BROWSER_FIREFOX);
            $this->setVersion($matches[1]);
            $this->setMobile(false);
            $this->setRobot(false);

            return true;
        }

        return false;
    }

    /**
     * Determine if the browser is iCab or not.
     * @access protected
     * @link http://www.icab.de/
     * @return boolean Returns true if the browser is iCab, false otherwise.
     */
    protected function checkBrowserIcab()
    {
        //Some (early) iCab versions don't use the standard "iCab/1.0", they uses "iCab 1.0;" instead
        return $this->checkSimpleBrowserUA('iCab', $this->_agent, self::BROWSER_ICAB);
    }

    /**
     * Determine if the browser is GNU IceCat (formerly known as GNU IceWeasel) or not.
     * @access protected
     * @link https://www.gnu.org/software/gnuzilla/
     * @return boolean Returns true if the browser is GNU IceCat, false otherwise.
     */
    protected function checkBrowserIceCat()
    {
        return $this->checkSimpleBrowserUA('IceCat', $this->_agent, self::BROWSER_ICECAT);
    }

    /**
     * Determine if the browser is GNU IceWeasel (now know as GNU IceCat) or not.
     * @access protected
     * @see checkBrowserIceCat()
     * @return boolean Returns true if the browser is GNU IceWeasel, false otherwise.
     */
    protected function checkBrowserIceWeasel()
    {
        return $this->checkSimpleBrowserUA('Iceweasel', $this->_agent, self::BROWSER_ICEWEASEL);
    }

    /**
     * Determine if the browser is Internet Explorer or not.
     * @access protected
     * @link https://www.microsoft.com/ie/
     * @link https://en.wikipedia.org/wiki/Internet_Explorer_Mobile
     * @return boolean Returns true if the browser is Internet Explorer, false otherwise.
     */
    protected function checkBrowserInternetExplorer()
    {
        //Test for Internet Explorer Mobile (formerly Pocket Internet Explorer)
        if ($this->checkSimpleBrowserUA(array('IEMobile', 'MSPIE'), $this->_agent, self::BROWSER_IE_MOBILE, true)) {
            return true;
        }

        //Several browsers uses IE compatibility UAs filter these browsers out (but after testing for IE Mobile)
        if ($this->containString($this->_agent, 'Opera') || $this->containString($this->_agent, array('BlackBerry', 'Nokia'), true, false)) {
            return false;
        }

        //Test for Internet Explorer 1
        if ($this->checkSimpleBrowserUA('Microsoft Internet Explorer', $this->_agent, self::BROWSER_IE)) {
            if ($this->getVersion() == self::VERSION_UNKNOWN) {
                if (preg_match('/308|425|426|474|0b1/i', $this->_agent)) {
                    $this->setVersion('1.5');
                } else {
                    $this->setVersion('1.0');
                }
            }
            return true;
        }

        //Test for Internet Explorer 2+
        if ($this->containString($this->_agent, array('MSIE', 'Trident'))) {
            $version = '';

            if ($this->containString($this->_agent, 'Trident')) {
                //Test for Internet Explorer 11+ (check the rv: string)
                if ($this->containString($this->_agent, 'rv:', true, false)) {
                    if ($this->checkSimpleBrowserUA('Trident', $this->_agent, self::BROWSER_IE, false, 'rv:')) {
                        return true;
                    }
                } else {
                    //Test for Internet Explorer 8, 9 & 10 (check the Trident string)
                    if (preg_match('/Trident\/([\d]+)/i', $this->_agent, $foundVersion)) {
                        //Trident started with version 4.0 on IE 8
                        $verFromTrident = $this->parseInt($foundVersion[1]) + 4;
                        if ($verFromTrident >= 8) {
                            $version = $verFromTrident . '.0';
                        }
                    }
                }

                //If we have the IE version from Trident, we can check for the compatibility view mode
                if ($version != '') {
                    $emulatedVer = '';
                    preg_match_all('/MSIE\s*([^\s;$]+)/i', $this->_agent, $foundVersions);
                    foreach ($foundVersions[1] as $currVer) {
                        //Keep the lowest MSIE version for the emulated version (in compatibility view mode)
                        if ($emulatedVer == '' || $this->compareVersions($emulatedVer, $currVer) == 1) {
                            $emulatedVer = $currVer;
                        }
                    }
                    //Set the compatibility view mode if $version != $emulatedVer
                    if ($this->compareVersions($version, $emulatedVer) != 0) {
                        $this->_compatibilityViewName = self::BROWSER_IE;
                        $this->_compatibilityViewVer = $this->cleanVersion($emulatedVer);
                    }
                }
            }

            //Test for Internet Explorer 2-7 versions if needed
            if ($version == '') {
                preg_match_all('/MSIE\s+([^\s;$]+)/i', $this->_agent, $foundVersions);
                foreach ($foundVersions[1] as $currVer) {
                    //Keep the highest MSIE version
                    if ($version == '' || $this->compareVersions($version, $currVer) == -1) {
                        $version = $currVer;
                    }
                }
            }

            $this->setBrowser(self::BROWSER_IE);
            $this->setVersion($version);
            $this->setMobile(false);
            $this->setRobot(false);

            return true;
        }

        return false;
    }

    /**
     * Determine if the browser is Konqueror or not.
     * @access protected
     * @link https://www.konqueror.org/
     * @return boolean Returns true if the browser is Konqueror, false otherwise.
     */
    protected function checkBrowserKonqueror()
    {
        return $this->checkSimpleBrowserUA('Konqueror', $this->_agent, self::BROWSER_KONQUEROR);
    }

    /**
     * Determine if the browser is Lynx or not. It is the oldest web browser currently in general use and development.
     * It is a text-based only Web browser.
     * @access protected
     * @link https://en.wikipedia.org/wiki/Lynx_(web_browser)
     * @return boolean Returns true if the browser is Lynx, false otherwise.
     */
    protected function checkBrowserLynx()
    {
        return $this->checkSimpleBrowserUA('Lynx', $this->_agent, self::BROWSER_LYNX);
    }

    /**
     * Determine if the browser is Mozilla or not.
     * @access protected
     * @return boolean Returns true if the browser is Mozilla, false otherwise.
     */
    protected function checkBrowserMozilla()
    {
        return $this->checkSimpleBrowserUA('Mozilla', $this->_agent, self::BROWSER_MOZILLA, false, 'rv:');
    }

    /**
     * Determine if the browser is MSN TV (formerly WebTV) or not.
     * @access protected
     * @link https://en.wikipedia.org/wiki/MSN_TV
     * @return boolean Returns true if the browser is WebTv, false otherwise.
     */
    protected function checkBrowserMsnTv()
    {
        return $this->checkSimpleBrowserUA('webtv', $this->_agent, self::BROWSER_MSNTV);
    }

    /**
     * Determine if the browser is Netscape or not. Official support for this browser ended on March 1st, 2008.
     * @access protected
     * @link https://en.wikipedia.org/wiki/Netscape
     * @return boolean Returns true if the browser is Netscape, false otherwise.
     */
    protected function checkBrowserNetscape()
    {
        //BlackBerry & Nokia UAs can conflict with Netscape UAs
        if ($this->containString($this->_agent, array('BlackBerry', 'Nokia'), true, false)) {
            return false;
        }

        //Netscape v6 to v9 check
        if ($this->checkSimpleBrowserUA(array('Netscape', 'Navigator', 'Netscape6'), $this->_agent, self::BROWSER_NETSCAPE)) {
            return true;
        }

        //Netscape v1-4 (v5 don't exists)
        $found = false;
        if ($this->containString($this->_agent, 'Mozilla') && !$this->containString($this->_agent, 'rv:', true, false)) {
            $version = '';
            $verParts = explode('/', stristr($this->_agent, 'Mozilla'));
            if (count($verParts) > 1) {
                $verParts = explode(' ', $verParts[1]);
                $verParts = explode('.', $verParts[0]);

                $majorVer = $this->parseInt($verParts[0]);
                if ($majorVer > 0 && $majorVer < 5) {
                    $version = implode('.', $verParts);
                    $found = true;

                    if (strtolower(substr($version, -4)) == '-sgi') {
                        $version = substr($version, 0, -4);
                    } else {
                        if (strtolower(substr($version, -4)) == 'gold') {
                            $version = substr($version, 0, -4) . ' Gold'; //Doubles spaces (if any) will be normalized by setVersion()
                        }
                    }
                }
            }
        }

        if ($found) {
            $this->setBrowser(self::BROWSER_NETSCAPE);
            $this->setVersion($version);
            $this->setMobile(false);
            $this->setRobot(false);
        }

        return $found;
    }

    /**
     * Determine if the browser is a Nokia browser or not.
     * @access protected
     * @link https://web.archive.org/web/20141012034159/http://www.developer.nokia.com/Community/Wiki/User-Agent_headers_for_Nokia_devices
     * @return boolean Returns true if the browser is a Nokia browser, false otherwise.
     */
    protected function checkBrowserNokia()
    {
        if ($this->containString($this->_agent, array('Nokia5800', 'Nokia5530', 'Nokia5230'), true, false)) {
            $this->setBrowser(self::BROWSER_NOKIA);
            $this->setVersion('7.0');
            $this->setMobile(true);
            $this->setRobot(false);

            return true;
        }

        if ($this->checkSimpleBrowserUA(array('NokiaBrowser', 'BrowserNG', 'Series60', 'S60', 'S40OviBrowser'), $this->_agent, self::BROWSER_NOKIA, true)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the browser is Opera or not.
     * @access protected
     * @link https://www.opera.com/
     * @link https://www.opera.com/mobile/
     * @link https://web.archive.org/web/20140220123653/http://my.opera.com/community/openweb/idopera/
     * @return boolean Returns true if the browser is Opera, false otherwise.
     */
    protected function checkBrowserOpera()
    {
        if ($this->checkBrowserUAWithVersion('Opera Mobi', $this->_agent, self::BROWSER_OPERA_MOBILE, true)) {
            return true;
        }

        if ($this->checkSimpleBrowserUA('Opera Mini', $this->_agent, self::BROWSER_OPERA_MINI, true)) {
            return true;
        }

        $version = '';
        $found = $this->checkBrowserUAWithVersion('Opera', $this->_agent, self::BROWSER_OPERA);
        if ($found && $this->getVersion() != self::VERSION_UNKNOWN) {
            $version = $this->getVersion();
        }

        if (!$found || $version == '') {
            if ($this->checkSimpleBrowserUA('Opera', $this->_agent, self::BROWSER_OPERA)) {
                return true;
            }
        }

        if (!$found && $this->checkSimpleBrowserUA('Chrome', $this->_agent, self::BROWSER_CHROME) ) {
            if ($this->checkSimpleBrowserUA('OPR', $this->_agent, self::BROWSER_OPERA)) {
                return true;
            }
        }

        return $found;
    }

    /**
     * Determine if the browser is Phoenix or not. Phoenix was the name of Firefox from version 0.1 to 0.5.
     * @access protected
     * @return boolean Returns true if the browser is Phoenix, false otherwise.
     */
    protected function checkBrowserPhoenix()
    {
        return $this->checkSimpleBrowserUA('Phoenix', $this->_agent, self::BROWSER_PHOENIX);
    }

    /**
     * Determine what is the browser used by the user.
     * @access protected
     * @return boolean Returns true if the browser has been identified, false otherwise.
     */
    protected function checkBrowser()
    {
        //Changing the check order can break the class detection results!
        return
               /* Major browsers and browsers that need to be detected in a special order */
               $this->checkBrowserCustom() ||           /* Customs rules are always checked first */
               $this->checkBrowserMsnTv() ||            /* MSN TV is based on IE so we must check for MSN TV before IE */
               $this->checkBrowserInternetExplorer() ||
               $this->checkBrowserOpera() ||            /* Opera must be checked before Firefox, Netscape and Chrome to avoid conflicts */
               $this->checkBrowserEdge() ||             /* Edge must be checked before Firefox, Safari and Chrome to avoid conflicts */
               $this->checkBrowserSamsung() ||          /* Samsung Internet browser must be checked before Chrome and Safari to avoid conflicts */
               $this->checkBrowserUC() ||               /* UC Browser must be checked before Chrome and Safari to avoid conflicts */
               $this->checkBrowserChrome() ||           /* Chrome must be checked before Netscaoe and Mozilla to avoid conflicts */
               $this->checkBrowserIcab() ||             /* Check iCab before Netscape since iCab have Mozilla UAs */
               $this->checkBrowserNetscape() ||         /* Must be checked before Firefox since Netscape 8-9 are based on Firefox */
               $this->checkBrowserIceCat() ||           /* Check IceCat and IceWeasel before Firefox since they are GNU builds of Firefox */
               $this->checkBrowserIceWeasel() ||
               $this->checkBrowserFirefox() ||
               /* Current browsers that don't need to be detected in any special order */
               $this->checkBrowserKonqueror() ||
               $this->checkBrowserLynx() ||
               /* Mobile */
               $this->checkBrowserAndroid() ||
               $this->checkBrowserBlackBerry() ||
               $this->checkBrowserNokia() ||
               /* WebKit base check (after most other checks) */
               $this->checkBrowserSafari() ||
               /* Deprecated browsers that don't need to be detected in any special order */
               $this->checkBrowserFirebird() ||
               $this->checkBrowserPhoenix() ||
               /* Mozilla is such an open standard that it must be checked last */
               $this->checkBrowserMozilla();
    }

    /**
     * Determine if the browser is Safari or not.
     * @access protected
     * @link https://www.apple.com/safari/
     * @link https://web.archive.org/web/20080514173941/http://developer.apple.com/internet/safari/uamatrix.html
     * @link https://en.wikipedia.org/wiki/Safari_version_history#Release_history
     * @return boolean Returns true if the browser is Safari, false otherwise.
     */
    protected function checkBrowserSafari()
    {
        $version = '';

        //Check for current versions of Safari
        $found = $this->checkBrowserUAWithVersion(array('Safari', 'AppleWebKit'), $this->_agent, self::BROWSER_SAFARI);
        if ($found && $this->getVersion() != self::VERSION_UNKNOWN) {
            $version = $this->getVersion();
        }

        //Safari 1-2 didn't had a "Version" string in the UA, only a WebKit build and/or Safari build, extract version from these...
        if (!$found || $version == '') {
            if (preg_match('/.*Safari[ (\/]*([a-z0-9.-]*)/i', $this->_agent, $matches)) {
                $version = $this->safariBuildToSafariVer($matches[1]);
                $found = true;
            }
        }
        if (!$found || $version == '') {
            if (preg_match('/.*AppleWebKit[ (\/]*([a-z0-9.-]*)/i', $this->_agent, $matches)) {
                $version = $this->webKitBuildToSafariVer($matches[1]);
                $found = true;
            }
        }

        if ($found) {
            $this->setBrowser(self::BROWSER_SAFARI);
            $this->setVersion($version);
            $this->setMobile(false);
            $this->setRobot(false);
        }

        return $found;
    }

    /**
     * Determine if the browser is the Samsung Internet browser or not.
     * @access protected
     * @return boolean Returns true if the browser is the the Samsung Internet browser, false otherwise.
     */
    protected function checkBrowserSamsung()
    {
        return $this->checkSimpleBrowserUA('SamsungBrowser', $this->_agent, self::BROWSER_SAMSUNG, true);
    }

    /**
     * Test the user agent for a specific browser that use a "Version" string (like Safari and Opera). The user agent
     * should look like: "Version/1.0 Browser name/123.456" or "Browser name/123.456 Version/1.0".
     * @access protected
     * @param mixed $uaNameToLookFor The string (or array of strings) representing the browser name to find in the user
     * agent.
     * @param string $userAgent The user agent string to work with.
     * @param string $browserName The literal browser name. Always use a class constant!
     * @param boolean $isMobile (optional) Determines if the browser is from a mobile device.
     * @param boolean $findWords (optional) Determines if the needle should match a word to be found. For example "Bar"
     * would not be found in "FooBar" when true but would be found in "Foo Bar". When set to false, the needle can be
     * found anywhere in the haystack.
     * @return boolean Returns true if we found the browser we were looking for, false otherwise.
     */
    protected function checkBrowserUAWithVersion($uaNameToLookFor, $userAgent, $browserName, $isMobile = false, $findWords = true)
    {
        if (!is_array($uaNameToLookFor)) {
            $uaNameToLookFor = array($uaNameToLookFor);
        }

        foreach ($uaNameToLookFor as $currUANameToLookFor) {
            if ($this->containString($userAgent, $currUANameToLookFor, true, $findWords)) {
                $version = '';
                $verParts = explode('/', stristr($this->_agent, 'Version'));
                if (count($verParts) > 1) {
                    $verParts = explode(' ', $verParts[1]);
                    $version = $verParts[0];
                }

                $this->setBrowser($browserName);
                $this->setVersion($version);

                $this->setMobile($isMobile);

                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the browser is UC Browser or not.
     * @access protected
     * @return boolean Returns true if the browser is UC Browser, false otherwise.
     */
    protected function checkBrowserUC()
    {
        return $this->checkSimpleBrowserUA('UCBrowser', $this->_agent, self::BROWSER_UC, true);
    }

    /**
     * Determine the user's platform.
     * @access protected
     */
    protected function checkPlatform()
    {
        if (!$this->checkPlatformCustom()) { /* Customs rules are always checked first */
            /* Mobile platforms */
            if ($this->containString($this->_agent, array('Windows Phone', 'IEMobile'))) { /* Check Windows Phone (formerly Windows Mobile) before Windows */
                $this->setPlatform(self::PLATFORM_WINDOWS_PHONE);
                $this->setMobile(true);
            } else if ($this->containString($this->_agent, 'Windows CE')) { /* Check Windows CE before Windows */
                $this->setPlatform(self::PLATFORM_WINDOWS_CE);
                $this->setMobile(true);
            } else if ($this->containString($this->_agent, array('CPU OS', 'CPU iPhone OS', 'iPhone', 'iPad', 'iPod'))) { /* Check iOS (iPad/iPod/iPhone) before Macintosh */
                $this->setPlatform(self::PLATFORM_IOS);
                $this->setMobile(true);
            } else if ($this->containString($this->_agent, 'Android')) {
                $this->setPlatform(self::PLATFORM_ANDROID);
                $this->setMobile(true);
            } else if ($this->containString($this->_agent, 'BlackBerry', true, false) || $this->containString($this->_agent, array('BB10', 'RIM Tablet OS'))) {
                $this->setPlatform(self::PLATFORM_BLACKBERRY);
                $this->setMobile(true);
            } else if ($this->containString($this->_agent, 'Nokia', true, false)) {
                $this->setPlatform(self::PLATFORM_NOKIA);
                $this->setMobile(true);

            /* Desktop platforms */
            } else if ($this->containString($this->_agent, 'Windows')) {
                $this->setPlatform(self::PLATFORM_WINDOWS);
            } else if ($this->containString($this->_agent, 'Macintosh')) {
                $this->setPlatform(self::PLATFORM_MACINTOSH);
            } else if ($this->containString($this->_agent, 'Linux')) {
                $this->setPlatform(self::PLATFORM_LINUX);
            } else if ($this->containString($this->_agent, 'FreeBSD')) {
                $this->setPlatform(self::PLATFORM_FREEBSD);
            } else if ($this->containString($this->_agent, 'OpenBSD')) {
                $this->setPlatform(self::PLATFORM_OPENBSD);
            } else if ($this->containString($this->_agent, 'NetBSD')) {
                $this->setPlatform(self::PLATFORM_NETBSD);

            /* Discontinued */
            } else if ($this->containString($this->_agent, array('Symbian', 'SymbianOS'))) {
                $this->setPlatform(self::PLATFORM_SYMBIAN);
                $this->setMobile(true);
            } else if ($this->containString($this->_agent, 'OpenSolaris')) {
                $this->setPlatform(self::PLATFORM_OPENSOLARIS);

            /* Generic */
            } else if ($this->containString($this->_agent, 'Win', true, false)) {
                $this->setPlatform(self::PLATFORM_WINDOWS);
            } else if ($this->containString($this->_agent, 'Mac', true, false)) {
                $this->setPlatform(self::PLATFORM_MACINTOSH);
            }
        }

        //Check if it's a 64-bit platform
        if ($this->containString($this->_agent, array('WOW64', 'Win64', 'AMD64', 'x86_64', 'x86-64', 'ia64', 'IRIX64',
                'ppc64', 'sparc64', 'x64;', 'x64_64'))) {
            $this->set64bit(true);
        }

        $this->checkPlatformVersion();
    }

    /**
     * Determine if the platform is among the custom platform rules or not. Rules are checked in the order they were
     * added.
     * @access protected
     * @return boolean Returns true if we found the platform we were looking for in the custom rules, false otherwise.
     */
    protected function checkPlatformCustom()
    {
        foreach ($this->_customPlatformDetection as $platformName => $customPlatform) {
            $platformNameToLookFor = $customPlatform['platformNameToLookFor'];
            $isMobile = $customPlatform['isMobile'];
            $findWords = $customPlatform['uaNameFindWords'];
            if ($this->containString($this->_agent, $platformNameToLookFor, true, $findWords)) {
                $this->setPlatform($platformName);
                if ($isMobile) {
                    $this->setMobile(true);
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Determine the user's platform version.
     * @access protected
     */
    protected function checkPlatformVersion()
    {
        $result = '';
        switch ($this->getPlatform()) {
            case self::PLATFORM_WINDOWS:
                if (preg_match('/Windows NT\s*(\d+(?:\.\d+)*)/i', $this->_agent, $foundVersion)) {
                    $result = 'NT ' . $foundVersion[1];
                } else {
                    //https://support.microsoft.com/en-us/kb/158238

                    if ($this->containString($this->_agent, array('Windows XP', 'WinXP', 'Win XP'))) {
                        $result = '5.1';
                    } else if ($this->containString($this->_agent, 'Windows 2000', 'Win 2000', 'Win2000')) {
                        $result = '5.0';
                    } else if ($this->containString($this->_agent, array('Win 9x 4.90', 'Windows ME', 'WinME', 'Win ME'))) {
                        $result = '4.90.3000'; //Windows Me version range from 4.90.3000 to 4.90.3000A
                    } else if ($this->containString($this->_agent, array('Windows 98', 'Win98', 'Win 98'))) {
                        $result = '4.10'; //Windows 98 version range from 4.10.1998 to 4.10.2222B
                    } else if ($this->containString($this->_agent, array('Windows 95', 'Win95', 'Win 95'))) {
                        $result = '4.00'; //Windows 95 version range from 4.00.950 to 4.03.1214
                    } else if (($foundAt = stripos($this->_agent, 'Windows 3')) !== false) {
                        $result = '3';
                        if (preg_match('/\d+(?:\.\d+)*/', substr($this->_agent, $foundAt + strlen('Windows 3')), $foundVersion)) {
                            $result .= '.' . $foundVersion[0];
                        }
                    } else if ($this->containString($this->_agent, 'Win16')) {
                        $result = '3.1';
                    }
                }
                break;

            case self::PLATFORM_MACINTOSH:
                if (preg_match('/Mac OS X\s*(\d+(?:_\d+)+)/i', $this->_agent, $foundVersion)) {
                    $result = str_replace('_', '.', $this->cleanVersion($foundVersion[1]));
                } else if ($this->containString($this->_agent, 'Mac OS X')) {
                    $result = '10';
                }
                break;

            case self::PLATFORM_ANDROID:
                if (preg_match('/Android\s+([^\s;$]+)/i', $this->_agent, $foundVersion)) {
                    $result = $this->cleanVersion($foundVersion[1]);
                }
                break;

            case self::PLATFORM_IOS:
                if (preg_match('/(?:CPU OS|iPhone OS|iOS)[\s_]*([\d_]+)/i', $this->_agent, $foundVersion)) {
                    $result = str_replace('_', '.', $this->cleanVersion($foundVersion[1]));
                }
                break;
        }

        if (trim($result) == '') {
            $result = self::PLATFORM_VERSION_UNKNOWN;
        }
        $this->setPlatformVersion($result);
    }

    /**
     * Determine if the robot is the Bingbot crawler or not.
     * @access protected
     * @link https://www.bing.com/webmaster/help/which-crawlers-does-bing-use-8c184ec0
     * @return boolean Returns true if the robot is Bingbot, false otherwise.
     */
    protected function checkRobotBingbot()
    {
        return $this->checkSimpleRobot('bingbot', $this->_agent, self::ROBOT_BINGBOT);
    }

    /**
     * Determine if the robot is the Googlebot crawler or not.
     * @access protected
     * @return boolean Returns true if the robot is Googlebot, false otherwise.
     */
    protected function checkRobotGooglebot()
    {
        if ($this->checkSimpleRobot('Googlebot', $this->_agent, self::ROBOT_GOOGLEBOT)) {
            if ($this->containString($this->_agent, 'googlebot-mobile')) {
                $this->setMobile(true);
            }

            return true;
        }

        return false;
    }

    /**
     * Determine if the robot is the MSNBot crawler or not. In October 2010 it was replaced by the Bingbot robot.
     * @access protected
     * @see checkRobotBingbot()
     * @return boolean Returns true if the robot is MSNBot, false otherwise.
     */
    protected function checkRobotMsnBot()
    {
        return $this->checkSimpleRobot('msnbot', $this->_agent, self::ROBOT_MSNBOT);
    }

    /**
     * Determine if it's a robot crawling the page and find it's name and version.
     * @access protected
     */
    protected function checkRobot()
    {
        $this->checkRobotCustom() || /* Customs rules are always checked first */
        $this->checkRobotGooglebot() ||
        $this->checkRobotBingbot() ||
        $this->checkRobotMsnBot() ||
        $this->checkRobotSlurp() ||
        $this->checkRobotYahooMultimedia() ||
        $this->checkRobotW3CValidator();
    }

    /**
     * Determine if the robot is among the custom robot rules or not. Rules are checked in the order they were added.
     * @access protected
     * @return boolean Returns true if we found the robot we were looking for in the custom rules, false otherwise.
     */
    protected function checkRobotCustom()
    {
        foreach ($this->_customRobotDetection as $robotName => $customRobot) {
            $uaNameToLookFor = $customRobot['uaNameToLookFor'];
            $isMobile = $customRobot['isMobile'];
            $separator = $customRobot['separator'];
            $uaNameFindWords = $customRobot['uaNameFindWords'];

            if ($this->checkSimpleRobot($uaNameToLookFor, $this->_agent, $robotName, $separator, $uaNameFindWords)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine if the robot is the Yahoo! Slurp crawler or not.
     * @access protected
     * @return boolean Returns true if the robot is Yahoo! Slurp, false otherwise.
     */
    protected function checkRobotSlurp()
    {
        return $this->checkSimpleRobot('Yahoo! Slurp', $this->_agent, self::ROBOT_SLURP);
    }

    /**
     * Determine if the robot is the W3C Validator or not.
     * @access protected
     * @link https://validator.w3.org/
     * @return boolean Returns true if the robot is the W3C Validator, false otherwise.
     */
    protected function checkRobotW3CValidator()
    {
        //Since the W3C validates pages with different robots we will prefix our versions with the part validated on the page...

        //W3C Link Checker (prefixed with "Link-")
        if ($this->checkSimpleRobot('W3C-checklink', $this->_agent, self::ROBOT_W3CVALIDATOR)) {
            if ($this->getRobotVersion() != self::ROBOT_VERSION_UNKNOWN) {
                $this->setRobotVersion('Link-' . $this->getRobotVersion());
            }
            return true;
        }

        //W3C CSS Validation Service (prefixed with "CSS-")
        if ($this->checkSimpleRobot('Jigsaw', $this->_agent, self::ROBOT_W3CVALIDATOR)) {
            if ($this->getRobotVersion() != self::ROBOT_VERSION_UNKNOWN) {
                $this->setRobotVersion('CSS-' . $this->getRobotVersion());
            }
            return true;
        }

        //W3C mobileOK Checker (prefixed with "mobileOK-")
        if ($this->checkSimpleRobot('W3C-mobileOK', $this->_agent, self::ROBOT_W3CVALIDATOR)) {
            if ($this->getRobotVersion() != self::ROBOT_VERSION_UNKNOWN) {
                $this->setRobotVersion('mobileOK-' . $this->getRobotVersion());
            }
            return true;
        }

        //W3C Markup Validation Service (no prefix)
        return $this->checkSimpleRobot('W3C_Validator', $this->_agent, self::ROBOT_W3CVALIDATOR);
    }

    /**
     * Determine if the robot is the Yahoo! multimedia crawler or not.
     * @access protected
     * @return boolean Returns true if the robot is the Yahoo! multimedia crawler, false otherwise.
     */
    protected function checkRobotYahooMultimedia()
    {
        return $this->checkSimpleRobot('Yahoo-MMCrawler', $this->_agent, self::ROBOT_YAHOO_MM);
    }

    /**
     * Test the user agent for a specific browser where the browser name is immediately followed by the version number.
     * The user agent should look like: "Browser name/1.0" or "Browser 1.0;".
     * @access protected
     * @param mixed $uaNameToLookFor The string (or array of strings) representing the browser name to find in the user
     * agent.
     * @param string $userAgent The user agent string to work with.
     * @param string $browserName The literal browser name. Always use a class constant!
     * @param boolean $isMobile (optional) Determines if the browser is from a mobile device.
     * @param string $separator (optional) The separator string used to split the browser name and the version number in
     * the user agent.
     * @param boolean $uaNameFindWords (optional) Determines if the browser name to find should match a word instead of
     * a part of a word. For example "Bar" would not be found in "FooBar" when true but would be found in "Foo Bar".
     * When set to false, the browser name can be found anywhere in the user agent string.
     * @return boolean Returns true if we found the browser we were looking for, false otherwise.
     */
    protected function checkSimpleBrowserUA($uaNameToLookFor, $userAgent, $browserName, $isMobile = false, $separator = '/', $uaNameFindWords = true)
    {
        if ($this->findAndGetVersion($uaNameToLookFor, $userAgent, $version, $separator, $uaNameFindWords)) {
            $this->setBrowser($browserName);
            $this->setVersion($version);

            $this->setMobile($isMobile);

            return true;
        }

        return false;
    }

    /**
     * Test the user agent for a specific robot where the robot name is immediately followed by the version number.
     * The user agent should look like: "Robot name/1.0" or "Robot 1.0;".
     * @access protected
     * @param mixed $uaNameToLookFor The string (or array of strings) representing the robot name to find in the user
     * agent.
     * @param string $userAgent The user agent string to work with.
     * @param string $robotName The literal robot name. Always use a class constant!
     * @param string $separator (optional) The separator string used to split the robot name and the version number in
     * the user agent.
     * @param boolean $uaNameFindWords (optional) Determines if the robot name to find should match a word instead of
     * a part of a word. For example "Bar" would not be found in "FooBar" when true but would be found in "Foo Bar".
     * When set to false, the robot name can be found anywhere in the user agent string.
     * @return boolean Returns true if we found the robot we were looking for, false otherwise.
     */
    protected function checkSimpleRobot($uaNameToLookFor, $userAgent, $robotName, $separator = '/', $uaNameFindWords = true)
    {
        if ($this->findAndGetVersion($uaNameToLookFor, $userAgent, $version, $separator, $uaNameFindWords)) {
            $this->setRobot(true);
            $this->setRobotName($robotName);
            $this->setRobotVersion($version);

            return true;
        }

        return false;
    }

    /**
     * Clean a version string from unwanted characters.
     * @access protected
     * @param string $version The version string to clean.
     * @return string Returns the cleaned version number string.
     */
    protected function cleanVersion($version)
    {
        //Clear anything that is in parentheses (and the parentheses themselves) - will clear started but unclosed ones too
        $cleanVer = preg_replace('/\([^)]+\)?/', '', $version);
        //Replace with a space any character which is NOT an alphanumeric, dot (.), hyphen (-), underscore (_) or space
        $cleanVer = preg_replace('/[^0-9.a-zA-Z_ -]/', ' ', $cleanVer);

        //Remove trailing and leading spaces
        $cleanVer = trim($cleanVer);

        //Remove trailing dot (.), hyphen (-), underscore (_)
        while (in_array(substr($cleanVer, -1), array('.', '-', '_'))) {
            $cleanVer = substr($cleanVer, 0, -1);
        }
        //Remove leading dot (.), hyphen (-), underscore (_) and character v
        while (in_array(substr($cleanVer, 0, 1), array('.', '-', '_', 'v', 'V'))) {
            $cleanVer = substr($cleanVer, 1);
        }

        //Remove double spaces if any
        while (strpos($cleanVer, '  ') !== false) {
            $cleanVer = str_replace('  ', ' ', $cleanVer);
        }

        return trim($cleanVer);
    }

    /**
     * Find if one or more substring is contained in a string.
     * @access protected
     * @param string $haystack The string to search in.
     * @param mixed $needle The string to search for. Can be a string or an array of strings if multiples values are to
     * be searched.
     * @param boolean $insensitive (optional) Determines if we do a case-sensitive search (false) or a case-insensitive
     * one (true).
     * @param boolean $findWords (optional) Determines if the needle should match a word to be found. For example "Bar"
     * would not be found in "FooBar" when true but would be found in "Foo Bar". When set to false, the needle can be
     * found anywhere in the haystack.
     * @return boolean Returns true if the needle (or one of the needles) has been found in the haystack, false
     * otherwise.
     */
    protected function containString($haystack, $needle, $insensitive = true, $findWords = true)
    {
        if (!is_array($needle)) {
            $needle = array($needle);
        }

        foreach ($needle as $currNeedle) {
            if ($findWords) {
                 $found = $this->wordPos($haystack, $currNeedle, $insensitive) !== false;
            } else {
                if ($insensitive) {
                    $found = stripos($haystack, $currNeedle) !== false;
                } else {
                    $found = strpos($haystack, $currNeedle) !== false;
                }
            }

            if ($found) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect the user environment from the details in the user agent string.
     * @access protected
     */
    protected function detect()
    {
        $this->checkBrowser();
        $this->checkPlatform(); //Check the platform after the browser since some platforms can change the mobile value
        $this->checkRobot();
    }

    /**
     * Test the user agent for a specific browser and extract it's version.
     * @access protected
     * @param type $uaNameToLookFor The string (or array of strings) representing the browser name to find in the user
     * agent.
     * @param type $userAgent The user agent string to work with.
     * @param type $version String buffer that will contain the version found (if any).
     * @param type $separator (optional) The separator string used to split the browser name and the version number in
     * the user agent.
     * @param type $uaNameFindWords (optional) Determines if the browser name to find should match a word instead of
     * a part of a word. For example "Bar" would not be found in "FooBar" when true but would be found in "Foo Bar".
     * When set to false, the browser name can be found anywhere in the user agent string.
     * @return boolean Returns true if we found the browser we were looking for, false otherwise.
     */
    protected function findAndGetVersion($uaNameToLookFor, $userAgent, &$version, $separator = '/', $uaNameFindWords = true)
    {
        $version = '';
        if (!is_array($uaNameToLookFor)) {
            $uaNameToLookFor = array($uaNameToLookFor);
        }

        foreach ($uaNameToLookFor as $currUANameToLookFor) {
            if ($this->containString($userAgent, $currUANameToLookFor, true, $uaNameFindWords)) {
                //Many browsers don't use the standard "Browser/1.0" format, they uses "Browser 1.0;" instead
                if (stripos($userAgent, $currUANameToLookFor . $separator) === false) {
                    $userAgent = str_ireplace($currUANameToLookFor . ' ', $currUANameToLookFor . $separator, $userAgent);
                }

                $verParts = explode($separator, stristr($userAgent, $currUANameToLookFor));
                if (count($verParts) > 1) {
                    $verParts = explode(' ', $verParts[1]);
                    $version = $verParts[0];
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Convert the iOS version numbers to the operating system name. For instance '2.0' returns 'iPhone OS 2.0'.
     * @access protected
     * @param string $iOSVer The iOS version numbers as a string.
     * @return string The operating system name.
     */
    protected function iOSVerToStr($iOSVer)
    {
        if ($this->compareVersions($iOSVer, '3.0') <= 0) {
            return 'iPhone OS ' . $iOSVer;
        } else {
            return 'iOS ' . $iOSVer;
        }
    }

    /**
     * Convert the macOS version numbers to the operating system name. For instance '10.7' returns 'Mac OS X Lion'.
     * @access protected
     * @param string $macVer The macOS version numbers as a string.
     * @return string The operating system name or the constant PLATFORM_VERSION_UNKNOWN if nothing match the version
     * numbers.
     */
    protected function macVerToStr($macVer)
    {
        //https://en.wikipedia.org/wiki/OS_X#Release_history

        if ($this->_platformVersion === '10') {
            return 'Mac OS X'; //Unspecified Mac OS X version
        } else if ($this->compareVersions($macVer, '10.15') >= 0 && $this->compareVersions($macVer, '10.16') < 0) {
            return 'macOS Catalina';
        } else if ($this->compareVersions($macVer, '10.14') >= 0 && $this->compareVersions($macVer, '10.15') < 0) {
            return 'macOS Mojave';
        } else if ($this->compareVersions($macVer, '10.13') >= 0 && $this->compareVersions($macVer, '10.14') < 0) {
            return 'macOS High Sierra';
        } else if ($this->compareVersions($macVer, '10.12') >= 0 && $this->compareVersions($macVer, '10.13') < 0) {
            return 'macOS Sierra';
        } else if ($this->compareVersions($macVer, '10.11') >= 0 && $this->compareVersions($macVer, '10.12') < 0) {
            return 'OS X El Capitan';
        } else if ($this->compareVersions($macVer, '10.10') >= 0 && $this->compareVersions($macVer, '10.11') < 0) {
            return 'OS X Yosemite';
        } else if ($this->compareVersions($macVer, '10.9') >= 0 && $this->compareVersions($macVer, '10.10') < 0) {
            return 'OS X Mavericks';
        } else if ($this->compareVersions($macVer, '10.8') >= 0 && $this->compareVersions($macVer, '10.9') < 0) {
            return 'OS X Mountain Lion';
        } else if ($this->compareVersions($macVer, '10.7') >= 0 && $this->compareVersions($macVer, '10.8') < 0) {
            return 'Mac OS X Lion';
        } else if ($this->compareVersions($macVer, '10.6') >= 0 && $this->compareVersions($macVer, '10.7') < 0) {
            return 'Mac OS X Snow Leopard';
        } else if ($this->compareVersions($macVer, '10.5') >= 0 && $this->compareVersions($macVer, '10.6') < 0) {
            return 'Mac OS X Leopard';
        } else if ($this->compareVersions($macVer, '10.4') >= 0 && $this->compareVersions($macVer, '10.5') < 0) {
            return 'Mac OS X Tiger';
        } else if ($this->compareVersions($macVer, '10.3') >= 0 && $this->compareVersions($macVer, '10.4') < 0) {
            return 'Mac OS X Panther';
        } else if ($this->compareVersions($macVer, '10.2') >= 0 && $this->compareVersions($macVer, '10.3') < 0) {
            return 'Mac OS X Jaguar';
        } else if ($this->compareVersions($macVer, '10.1') >= 0 && $this->compareVersions($macVer, '10.2') < 0) {
            return 'Mac OS X Puma';
        } else if ($this->compareVersions($macVer, '10.0') >= 0 && $this->compareVersions($macVer, '10.1') < 0) {
            return 'Mac OS X Cheetah';
        } else {
            return self::PLATFORM_VERSION_UNKNOWN; //Unknown/unnamed Mac OS version
        }
    }

    /**
     * Get the integer value of a string variable.
     * @access protected
     * @param string $intStr The scalar value being converted to an integer.
     * @return int The integer value of $intStr on success, or 0 on failure.
     */
    protected function parseInt($intStr)
    {
        return intval($intStr, 10);
    }

    /**
     * Reset all the properties of the class.
     * @access protected
     */
    protected function reset()
    {
        $this->_agent = '';
        $this->_browserName = self::BROWSER_UNKNOWN;
        $this->_compatibilityViewName = '';
        $this->_compatibilityViewVer = '';
        $this->_is64bit = false;
        $this->_isMobile = false;
        $this->_isRobot = false;
        $this->_platform = self::PLATFORM_UNKNOWN;
        $this->_platformVersion = self::PLATFORM_VERSION_UNKNOWN;
        $this->_robotName = self::ROBOT_UNKNOWN;
        $this->_robotVersion = self::ROBOT_VERSION_UNKNOWN;
        $this->_version = self::VERSION_UNKNOWN;
    }

    /**
     * Convert a Safari build number to a Safari version number.
     * @access protected
     * @param string $version A string representing the version number.
     * @link https://web.archive.org/web/20080514173941/http://developer.apple.com/internet/safari/uamatrix.html
     * @return string Returns the Safari version string. If the version can't be determined, an empty string is
     * returned.
     */
    protected function safariBuildToSafariVer($version)
    {
        $verParts = explode('.', $version);

        //We need a 3 parts version (version 2 will becomes 2.0.0)
        while (count($verParts) < 3) {
            $verParts[] = 0;
        }
        foreach ($verParts as $i => $currPart) {
            $verParts[$i] = $this->parseInt($currPart);
        }

        switch ($verParts[0]) {
            case 419: $result = '2.0.4';
                break;
            case 417: $result = '2.0.3';
                break;
            case 416: $result = '2.0.2';
                break;

            case 412:
                if ($verParts[1] >= 5) {
                    $result = '2.0.1';
                } else {
                    $result = '2.0';
                }
                break;

            case 312:
                if ($verParts[1] >= 5) {
                    $result = '1.3.2';
                } else {
                    if ($verParts[1] >= 3) {
                        $result = '1.3.1';
                    } else {
                        $result = '1.3';
                    }
                }
                break;

            case 125:
                if ($verParts[1] >= 11) {
                    $result = '1.2.4';
                } else {
                    if ($verParts[1] >= 9) {
                        $result = '1.2.3';
                    } else {
                        if ($verParts[1] >= 7) {
                            $result = '1.2.2';
                        } else {
                            $result = '1.2';
                        }
                    }
                }
                break;

            case 100:
                if ($verParts[1] >= 1) {
                    $result = '1.1.1';
                } else {
                    $result = '1.1';
                }
                break;

            case 85:
                if ($verParts[1] >= 8) {
                    $result = '1.0.3';
                } else {
                    if ($verParts[1] >= 7) {
                        $result = '1.0.2';
                    } else {
                        $result = '1.0';
                    }
                }
                break;

            case 73: $result = '0.9';
                break;
            case 51: $result = '0.8.1';
                break;
            case 48: $result = '0.8';
                break;

            default: $result = '';
        }

        return $result;
    }

    /**
     * Set if the browser is executed from a 64-bit platform.
     * @access protected
     * @param boolean $is64bit Value that tells if the browser is executed from a 64-bit platform.
     */
    protected function set64bit($is64bit)
    {
        $this->_is64bit = $is64bit == true;
    }

    /**
     * Set the name of the browser.
     * @access protected
     * @param string $browserName The name of the browser.
     */
    protected function setBrowser($browserName)
    {
        $this->_browserName = $browserName;
    }

    /**
     * Set the browser to be from a mobile device or not.
     * @access protected
     * @param boolean $isMobile (optional) Value that tells if the browser is on a mobile device or not.
     */
    protected function setMobile($isMobile = true)
    {
        $this->_isMobile = $isMobile == true;
    }

    /**
     * Set the platform on which the browser is on.
     * @access protected
     * @param string $platform The name of the platform.
     */
    protected function setPlatform($platform)
    {
        $this->_platform = $platform;
    }

    /**
     * Set the platform version on which the browser is on.
     * @access protected
     * @param string $platformVer The version numbers of the platform.
     */
    protected function setPlatformVersion($platformVer)
    {
        $this->_platformVersion = $platformVer;
    }

    /**
     * Set the browser to be a robot (crawler) or not.
     * @access protected
     * @param boolean $isRobot (optional) Value that tells if the browser is a robot or not.
     */
    protected function setRobot($isRobot = true)
    {
        $this->_isRobot = $isRobot == true;
    }

    /**
     * Set the name of the robot.
     * @access protected
     * @param string $robotName The name of the robot.
     */
    protected function setRobotName($robotName)
    {
        $this->_robotName = $robotName;
    }

    /**
     * Set the version of the robot.
     * @access protected
     * @param string $robotVersion The version of the robot.
     */
    protected function setRobotVersion($robotVersion)
    {
        $cleanVer = $this->cleanVersion($robotVersion);

        if ($cleanVer == '') {
            $this->_robotVersion = self::ROBOT_VERSION_UNKNOWN;
        } else {
            $this->_robotVersion = $cleanVer;
        }
    }

    /**
     * Set the version of the browser.
     * @access protected
     * @param string $version The version of the browser.
     */
    protected function setVersion($version)
    {
        $cleanVer = $this->cleanVersion($version);

        if ($cleanVer == '') {
            $this->_version = self::VERSION_UNKNOWN;
        } else {
            $this->_version = $cleanVer;
        }
    }

    /**
     * Convert a WebKit build number to a Safari version number.
     * @access protected
     * @param string $version A string representing the version number.
     * @link https://web.archive.org/web/20080514173941/http://developer.apple.com/internet/safari/uamatrix.html
     * @return string Returns the Safari version string. If the version can't be determined, an empty string is
     * returned.
     */
    protected function webKitBuildToSafariVer($version)
    {
        $verParts = explode('.', $version);

        //We need a 3 parts version (version 2 will becomes 2.0.0)
        while (count($verParts) < 3) {
            $verParts[] = 0;
        }
        foreach ($verParts as $i => $currPart) {
            $verParts[$i] = $this->parseInt($currPart);
        }

        switch ($verParts[0]) {
            case 419: $result = '2.0.4';
                break;

            case 418:
                if ($verParts[1] >= 8) {
                    $result = '2.0.4';
                } else {
                    $result = '2.0.3';
                }
                break;

            case 417: $result = '2.0.3';
                break;

            case 416: $result = '2.0.2';
                break;

            case 412:
                if ($verParts[1] >= 7) {
                    $result = '2.0.1';
                } else {
                    $result = '2.0';
                }
                break;

            case 312:
                if ($verParts[1] >= 8) {
                    $result = '1.3.2';
                } else {
                    if ($verParts[1] >= 5) {
                        $result = '1.3.1';
                    } else {
                        $result = '1.3';
                    }
                }
                break;

            case 125:
                if ($this->compareVersions('5.4', $verParts[1] . '.' . $verParts[2]) == -1) {
                    $result = '1.2.4'; //125.5.5+
                } else {
                    if ($verParts[1] >= 4) {
                        $result = '1.2.3';
                    } else {
                        if ($verParts[1] >= 2) {
                            $result = '1.2.2';
                        } else {
                            $result = '1.2';
                        }
                    }
                }
                break;

            //WebKit 100 can be either Safari 1.1 (Safari build 100) or 1.1.1 (Safari build 100.1)
            //for this reason, check the Safari build before the WebKit build.
            case 100: $result = '1.1.1';
                break;

            case 85:
                if ($verParts[1] >= 8) {
                    $result = '1.0.3';
                } else {
                    if ($verParts[1] >= 7) {
                        //WebKit 85.7 can be either Safari 1.0 (Safari build 85.5) or 1.0.2 (Safari build 85.7)
                        //for this reason, check the Safari build before the WebKit build.
                        $result = '1.0.2';
                    } else {
                        $result = '1.0';
                    }
                }
                break;

            case 73: $result = '0.9';
                break;
            case 51: $result = '0.8.1';
                break;
            case 48: $result = '0.8';
                break;

            default: $result = '';
        }

        return $result;
    }

    /**
     * Convert the Windows NT family version numbers to the operating system name. For instance '5.1' returns
     * 'Windows XP'.
     * @access protected
     * @param string $winVer The Windows NT family version numbers as a string.
     * @param boolean $returnServerFlavor (optional) Since some Windows NT versions have the same values, this flag
     * determines if the Server flavor is returned or not. For instance Windows 8.1 and Windows Server 2012 R2 both use
     * version 6.3.
     * @return string The operating system name or the constant PLATFORM_VERSION_UNKNOWN if nothing match the version
     * numbers.
     */
    protected function windowsNTVerToStr($winVer, $returnServerFlavor = false)
    {
        //https://en.wikipedia.org/wiki/List_of_Microsoft_Windows_versions

        $cleanWinVer = explode('.', $winVer);
        while (count($cleanWinVer) > 2) {
            array_pop($cleanWinVer);
        }
        $cleanWinVer = implode('.', $cleanWinVer);

        if ($this->compareVersions($cleanWinVer, '11') >= 0) {
            //Future versions of Windows
            return self::PLATFORM_WINDOWS . ' ' . $winVer;
        } else if ($this->compareVersions($cleanWinVer, '10') >= 0) {
            //Current version of Windows
            //(Windows Server 2019 & 2016 have the same version number. Only the build can separate the two - which is not included in the UA)
            return $returnServerFlavor ? (self::PLATFORM_WINDOWS . ' Server 2019') : (self::PLATFORM_WINDOWS . ' 10');
        } else if ($this->compareVersions($cleanWinVer, '7') < 0) {
            if ($this->compareVersions($cleanWinVer, '6.3') == 0) {
                return $returnServerFlavor ? (self::PLATFORM_WINDOWS . ' Server 2012 R2') : (self::PLATFORM_WINDOWS . ' 8.1');
            } else if ($this->compareVersions($cleanWinVer, '6.2') == 0) {
                return $returnServerFlavor ? (self::PLATFORM_WINDOWS . ' Server 2012') : (self::PLATFORM_WINDOWS . ' 8');
            } else if ($this->compareVersions($cleanWinVer, '6.1') == 0) {
                return $returnServerFlavor ? (self::PLATFORM_WINDOWS . ' Server 2008 R2') : (self::PLATFORM_WINDOWS . ' 7');
            } else if ($this->compareVersions($cleanWinVer, '6') == 0) {
                return $returnServerFlavor ? (self::PLATFORM_WINDOWS . ' Server 2008') : (self::PLATFORM_WINDOWS . ' Vista');
            } else if ($this->compareVersions($cleanWinVer, '5.2') == 0) {
                return $returnServerFlavor ? (self::PLATFORM_WINDOWS . ' Server 2003 / ' . self::PLATFORM_WINDOWS . ' Server 2003 R2') : (self::PLATFORM_WINDOWS . ' XP x64 Edition');
            } else if ($this->compareVersions($cleanWinVer, '5.1') == 0) {
                return self::PLATFORM_WINDOWS . ' XP';
            } else if ($this->compareVersions($cleanWinVer, '5') == 0) {
                return self::PLATFORM_WINDOWS . ' 2000';
            } else if ($this->compareVersions($cleanWinVer, '5') < 0 && $this->compareVersions($cleanWinVer, '3') >= 0) {
                return self::PLATFORM_WINDOWS . ' NT ' . $winVer;
            }
        }

        return self::PLATFORM_VERSION_UNKNOWN; //Invalid Windows NT version
    }

    /**
     * Convert the Windows 3.x & 9x family version numbers to the operating system name. For instance '4.10.1998'
     * returns 'Windows 98'.
     * @access protected
     * @param string $winVer The Windows 3.x or 9x family version numbers as a string.
     * @return string The operating system name or the constant PLATFORM_VERSION_UNKNOWN if nothing match the version
     * numbers.
     */
    protected function windowsVerToStr($winVer)
    {
        //https://support.microsoft.com/en-us/kb/158238

        if ($this->compareVersions($winVer, '4.90') >= 0 && $this->compareVersions($winVer, '4.91') < 0) {
            return self::PLATFORM_WINDOWS . ' Me'; //Normally range from 4.90.3000 to 4.90.3000A
        } else if ($this->compareVersions($winVer, '4.10') >= 0 && $this->compareVersions($winVer, '4.11') < 0) {
            return self::PLATFORM_WINDOWS . ' 98'; //Normally range from 4.10.1998 to 4.10.2222B
        } else if ($this->compareVersions($winVer, '4') >= 0 && $this->compareVersions($winVer, '4.04') < 0) {
            return self::PLATFORM_WINDOWS . ' 95'; //Normally range from 4.00.950 to 4.03.1214
        } else if ($this->compareVersions($winVer, '3.1') == 0 || $this->compareVersions($winVer, '3.11') == 0) {
            return self::PLATFORM_WINDOWS . ' ' . $winVer;
        } else if ($this->compareVersions($winVer, '3.10') == 0) {
            return self::PLATFORM_WINDOWS . ' 3.1';
        } else {
            return self::PLATFORM_VERSION_UNKNOWN; //Invalid Windows version
        }
    }

    /**
     * Find the position of the first occurrence of a word in a string.
     * @access protected
     * @param string $haystack The string to search in.
     * @param string $needle The string to search for.
     * @param boolean $insensitive (optional) Determines if we do a case-sensitive search (false) or a case-insensitive
     * one (true).
     * @param int $offset If specified, search will start this number of characters counted from the beginning of the
     * string. If the offset is negative, the search will start this number of characters counted from the end of the
     * string.
     * @param string $foundString String buffer that will contain the exact matching needle found. Set to NULL when
     * return value of the function is false.
     * @return mixed Returns the position of the needle (int) if found, false otherwise. Warning this function may
     * return Boolean false, but may also return a non-Boolean value which evaluates to false.
     */
    protected function wordPos($haystack, $needle, $insensitive = true, $offset = 0, &$foundString = NULL)
    {
        if ($offset != 0) {
            $haystack = substr($haystack, $offset);
        }

        $parts = explode(' ', $needle);
        foreach ($parts as $i => $currPart) {
            $parts[$i] = preg_quote($currPart, '/');
        }

        $regex = '/(?<=\A|[\s\/\\.,;:_()-])' . implode('[\s\/\\.,;:_()-]', $parts) . '(?=[\s\/\\.,;:_()-]|$)/';
        if ($insensitive) {
             $regex .= 'i';
        }

        if (preg_match($regex, $haystack, $matches, PREG_OFFSET_CAPTURE)) {
            $foundString = $matches[0][0];
            return (int)$matches[0][1];
        }

        return false;
    }
}