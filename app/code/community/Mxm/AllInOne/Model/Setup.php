<?php

class Mxm_AllInOne_Model_Setup
{
    /**
     * @var Mage_Core_Model_Website
     */
    protected $website = null;

    /**
     * @var int
     */
    protected $websiteOffset;

    /**
     * @var int
     */
    protected $websiteCount;

    /**
     * @var array
     */
    protected $setupFiles = null;

    /**
     * @var int
     */
    protected $fileOffset;

    /**
     * @var int
     */
    protected $fileCount;

    /**
     * @var string
     */
    protected $currentSetupVersion;

    /**
     * @var array
     */
    protected $tasks = array();

    /**
     * @var array
     */
    protected $complete = array();

    /**
     * @var Mxm_AllInOne_Model_Setup_Facts
     */
    protected $facts = null;

    /**
     * Run the setup process
     */
    public function run()
    {
        /* @var $helper Mxm_AllInOne_Helper_Data */
        $helper = Mage::helper('mxmallinone');
        if ($helper->isSetUp() || $helper->isSettingUp() || $helper->hasSetupFailed()) {
            return;
        }
        $helper->toggleSettingUp(true);
        $moduleVersion = (string)$helper->getVersion();
        $websites = Mage::app()->getWebsites();
        $this->websiteCount = count($websites);
        $this->websiteOffset = 0;
        $failed = false;
        foreach ($websites as $website) {
            $this->website = $website;
            $this->fileOffset = 0;
            $this->complete = array();
            if ($helper->isSetupRequired($website)) {
                $this->updateProgress();
                $setupVersion = $helper->getSetupVersion($website);
                try {
                    $this->performSetup($setupVersion);
                    $helper->setSetupVersion($moduleVersion, $website);
                } catch (Exception $e) {
                    $failed = true;
                    Mage::logException(new Exception(
                        "Failed to set up customer space for website {$website->getCode()}",
                        null,
                        $e
                    ));
                }
            }
            // the facts no longer apply, clear them
            $this->getFacts()->clearFacts();
            $this->websiteOffset++;
        }
        $helper->toggleSettingUp(false);
        if (!$failed) {
            $helper->setSetupVersion($moduleVersion);
        } else {
            $helper->toggleSetupFailed(true);
        }
    }

    /**
     * Perform the setup to bring the customer space from its current version
     * to the version of this module
     *
     * @param string $currentVersion
     */
    protected function performSetup($currentVersion)
    {
        $setupFilesAll = $this->getSetupFiles();
        $setupFiles = array();
        foreach ($setupFilesAll as $version => $setupFile) {
            if (version_compare($version, $currentVersion) > 0) {
                $setupFiles[$version] = $setupFile;
            }
        }

        $this->applySetupFiles($setupFiles);
    }

    /**
     * Retrieve the list of setup files
     *
     * @return array
     */
    protected function getSetupFiles()
    {
        if ($this->setupFiles === null) {
            $dir = Mage::helper('mxmallinone')->getModuleDir('setup');

            $this->setupFiles = array();
            // Matches files with syntax such as setup-0.1.0.php
            $regSetupFile = '/^setup-((?:(?:\d)+?\.){2}(?:\d)+?)\.php$/i';
            $handlerDir = dir($dir);
            while (false !== ($file = $handlerDir->read())) {
                $matches = array();
                if (preg_match($regSetupFile, $file, $matches)) {
                    $version = $matches[1];
                    $this->setupFiles[$version] = $dir . DS . $file;
                }
            }
            $handlerDir->close();

            uksort($this->setupFiles, 'version_compare');
        }
        return $this->setupFiles;
    }

    /**
     * Work through the files collecting the setup tasks, then run the tasks
     *
     * @param array $files
     * @throws Exception
     */
    protected function applySetupFiles($files)
    {
        $this->fileCount  = count($files);
        foreach ($files as $version => $file) {
            try {
                $this->_log("Applying setup script for version $version...", 0);
                $this->tasks = array();
                $this->complete = array();
                $this->currentSetupVersion = $version;
                include $file;
                $this->runTasks();
                $this->fileOffset++;
                $this->_log('Done.', 0);
            } catch (Exception $e) {
                throw new Exception("Setup of customer space failed for version $version", null, $e);
            }
        }
    }

    /**
     * Work through the tasks for the current setup script.
     * This handles dependencies between tasks and long running tasks which
     * require regular checking to see if they have completed
     *
     * @throws Exception
     */
    protected function runTasks()
    {
        if (empty($this->tasks)) {
            return;
        }
        // 2 minute timeout per task to perform whole setup
        $timeout = time() + (120 * count($this->tasks));
        while(count($this->complete) < count($this->tasks) && time() < $timeout) {
            /* @var $task Mxm_AllInOne_Model_Setup_Task */
            foreach ($this->tasks as $name => $task) {
                if (isset($this->complete[$name])) {
                    continue;
                }
                if (!$task->hasRun()) {
                    if (!$task->canRun($this->tasks, $this->complete)) {
                        // Cannot yet run task due to dependencies
                        continue;
                    }
                    $this->_log("Running task: {$task->getDescription()}...");

                    $task->run();
                }
                if ($task->isComplete()) {
                    $this->_log("Task complete: {$task->getDescription()}");
                    $this->complete[$name] = $task;
                }
                $this->updateProgress();
            }
            usleep(10000); // hundredth of a second
        }
        if (count($this->complete) < count($this->tasks)) {
            throw new Exception(
                "Could not complete all tasks for setup due to timeout.\n" .
                'Tasks: ' . count($this->tasks) . "\n" .
                'Complete: ' . count($this->complete)
            );
        }
    }

    /**
     * Create a new task within the current setup script
     *
     * @param array $data
     */
    public function addTask($data)
    {
        $data['setup'] = $this;
        $dir = Mage::helper('mxmallinone')->getModuleDir('setup');
        $data['base_dir'] = $dir . DS . $this->currentSetupVersion;
        $this->tasks[$data['name']] = Mage::getModel('mxmallinone/setup_task', $data);
    }

    /**
     * Returns true if this magento installation has more than one store
     *
     * @return boolean
     */
    public function isMultiStore()
    {
        return $this->websiteCount > 1 || count($this->getStores()) > 1;
    }

    /**
     * Get the current website which we are performing setup for
     *
     * @return Mage_Core_Model_Website
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Get the stores for the current website
     *
     * @return array
     */
    public function getStores()
    {
        return $this->getWebsite()->getStores();
    }

    /**
     * Get the api instance for a service
     *
     * @param string $service
     * @return Mxm_AllInOne_Model_Api|Mxm_AllInOne_Model_Api_Json
     */
    public function getApi($service = null)
    {
        if (is_null($service)) {
            return Mage::helper('mxmallinone')->getApi($this->getWebsite()->getId());
        }
        return Mage::helper('mxmallinone')->getApi($this->getWebsite()->getId())->getInstance($service);
    }

    /**
     * Update the value of the progress flag. Worked out as proportion of websites
     * complete with proportion of files complete with proportion of
     * current tasks complete
     */
    protected function updateProgress()
    {
        if (!$this->fileCount || empty($this->tasks)) {
            Mage::helper('mxmallinone')->updateSetupProgress(0);
        } else {
            $value  = $this->websiteOffset / $this->websiteCount;
            $value += $this->fileOffset / ($this->websiteCount * $this->fileCount);
            $value += count($this->complete) / ($this->websiteCount * $this->fileCount * count($this->tasks));

            Mage::helper('mxmallinone')->updateSetupProgress(round($value * 100));
        }
    }

    public function getFacts()
    {
        if (is_null($this->facts)) {
            $this->facts = Mage::getModel('mxmallinone/setup_facts', $this);
        }
        return $this->facts;
    }

    /**
     * Log the current state within a setup script
     *
     * @param string $message
     * @param int $level
     */
    public function log($message, $level = 0)
    {
        $this->_log($message, $level + 2);
    }

    /**
     * Log the current overall state
     *
     * @param string $message
     * @param int $level
     */
    protected function _log($message, $level = 1)
    {
        if (Mage::helper('mxmallinone')->isDevel()) {
            Mage::log(
                str_repeat("  ", $level) .
                "> $message"
            );
        }
    }
}
