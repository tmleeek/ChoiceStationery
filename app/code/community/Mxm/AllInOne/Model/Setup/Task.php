<?php

/**
 * Task model
 *
 * @method string getName()
 * @method Mxm_AllInOne_Model_Setup_Task setName()
 * @method string getDescription()
 * @method Mxm_AllInOne_Model_Setup_Task setDescription()
 * @method closure getFunction()
 * @method Mxm_AllInOne_Model_Setup_Task setFunction()
 * @method closure getCheck()
 * @method Mxm_AllInOne_Model_Setup_Task setCheck()
 * @method array getDepends()
 * @method Mxm_AllInOne_Model_Setup_Task setDepends()
 * @method boolean getRun()
 * @method Mxm_AllInOne_Model_Setup_Task setRun()
 * @method Mxm_AllInOne_Model_Setup getSetup()
 * @method string getBaseDir()
 * @method Mxm_AllInOne_Model_Setup_Task setBaseDir()
 */
class Mxm_AllInOne_Model_Setup_Task extends Mage_Core_Model_Abstract
{
    /**
     * Returns the timeout for running this task in seconds.
     * If value has not been set, it will default to 10 seconds
     *
     * @return int
     */
    public function getTimeout()
    {
        if (!$this->hasData('timeout')) {
            $this->setTimeout(10);
        }
        return $this->getData('timeout');
    }

    /**
     * Check if this task can run
     *
     * @param array $tasks
     * @param array $complete
     * @return boolean
     */
    public function canRun($tasks, $complete)
    {
        $canRun = true;
        if ($this->hasDepends()) {
            $depends = (array)$this->getDepends();
            foreach ($depends as $depend) {
                if (isset($tasks[$depend]) && !isset($complete[$depend])) {
                    $canRun = false;
                }
            }
        }
        return $canRun;
    }

    /**
     *
     * @return \Mxm_AllInOne_Model_Setup_Task
     */
    public function run()
    {
        $file = $this->getBaseDir() . DS . $this->getName() . '.php';

        if (!file_exists($file)) {
            throw new Exception("Setup file '$file' does not exist");
        }

        include $file;

        $this->setRun(true);
        $this->setRunTs(time());

        return $this;
    }

    /**
     * @return boolean
     */
    public function runCheck()
    {
        if (!$this->hasCheck()) {
            return true;
        }

        if (time() > $this->getRunTs() + $this->getTimeout()) {
            throw new Exception("Task {$this->getName()} did not complete due to timeout");
        }

        if (!$this->hasCheckFile()) {
            $file = $this->getBaseDir() . DS . $this->getName() . '_check.php';

            if (!file_exists($file)) {
                // fall back to using the original run script
                $file = $this->getBaseDir() . DS . $this->getName() . '.php';
                if (!file_exists($file)) {
                    throw new Exception("Setup check file '$file' does not exist");
                }
            }
            $this->setCheckFile($file);
        }

        return (!!include($this->getCheckFile()));
    }

    /**
     * Check if the task has completed
     *
     * @return boolean
     */
    public function isComplete()
    {
        if ($this->hasComplete()) {
            return $this->getComplete();
        }
        if (!$this->hasRun()) {
            return false;
        }
        $complete = $this->runCheck();
        if ($complete === true) {
            $this->setComplete(true);
            return true;
        }
        return false;
    }

    /**
     * Get the content from a template content file
     *
     * @param string $name
     * @param array $variables
     * @return string
     */
    public function getContentFromTemplate($name, $variables = array())
    {
        $path = $this->getBaseDir() . DS . 'files' . DS . $name;
        if (!file_exists($path)) {
            throw new Exception("Template file $path does not exist");
        }
        extract($variables);
        ob_start();
        include $path;
        return ob_get_clean();
    }
}
