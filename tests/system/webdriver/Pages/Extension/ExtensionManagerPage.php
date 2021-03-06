<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Class for the back-end control panel screen.
 *
 * @since  3.3
 */
class ExtensionManagerPage extends AdminManagerPage
{
	protected $waitForXpath = "//div[@id = 'installer-install']";

	protected $url = 'administrator/index.php?option=com_installer';

	/**
	 * Function to install the Component
	 *
	 * @param   object  $cfg  Configuration Object
	 *
	 * @return void
	 */
	public function installComLocalise($cfg)
	{
		$comLocalisePath = $cfg->folder;
		$elementObject = $this->driver;
		$elementObject->findElement(
			By::xPath("//a[contains(text(),'Install from Directory')]")
		)->click();
		$elementObject->waitForElementUntilIsPresent(By::xPath('//input[@id="install_directory"]'), 50);
		$installFromDirectoryInput = $elementObject->findElement(By::xPath('//input[@id="install_directory"]'));
		$installFromDirectoryInput->clear();
		$this->buildPackage($cfg);
		$installFromDirectoryInput->sendKeys($comLocalisePath . $cfg->installPath);
		$elementObject->findElement(
			By::xPath(
				'//input[@onclick="Joomla.submitbutton3()"]'
			)
		)->click();
		$elementObject->waitForElementUntilIsPresent(
			By::xPath(
				'//div[@class="alert alert-success"]'
			), 30
		);
	}

	/**
	 * Function to Verify the Installation of the component
	 *
	 * @param   Object  $cfg            Configuration Object
	 * @param   string  $extensionName  Name of the Extension
	 *
	 * @return bool
	 */
	public function verifyInstallation($cfg, $extensionName = 'Localise')
	{
		$elementObject = $this->driver;
		$elementObject->get($cfg->host . $cfg->path . 'administrator/index.php?option=com_installer&view=manage');
		$elementObject->waitForElementUntilIsPresent(
			By::xPath(
				'//input[@id="filter_search"]'
			), 30
		);
		$search_filter = $elementObject->findElement(
			By::xPath(
				'//input[@id="filter_search"]'
			)
		);
		$search_filter->clear();
		$search_filter->sendKeys($extensionName);

		$elementObject->findElement(
			By::xPath(
				'//button[@title="Search" or @data-original-title="Search"]'
			)
		)->click();
		$elementObject->waitForElementUntilIsPresent(
			By::xPath(
				'//input[@id="filter_search"]'
			), 30
		);
		$row = $this->getRowNumber($extensionName) - 1;
		$elementObject->waitForElementUntilIsPresent(
			By::xPath(
				'//input[@id="cb' . $row . '"]'
			), 30
		);
		$arrayElement = $elementObject->findElements(
			By::xPath(
				'//tbody/tr/td[2]//span[contains(text(),"' . $extensionName . '")]'
			)
		);

		if (count($arrayElement))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Run the build script to have the installation folder and ZIP
	 *
	 * @param   Object  $cfg  Configuration Object
	 *
	 * @return void
	 */
	private function buildPackage($cfg)
	{
		$current_dir = getcwd();
		chdir($cfg->folder . 'build/');
		shell_exec('build.sh');
		chdir($current_dir);
	}
}
