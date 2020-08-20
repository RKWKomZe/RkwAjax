<?php

namespace RKW\RkwAjax\Encoder;
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


/**
 * Class AbstracJsonEncoder
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractJsonEncoder
{

    /**
     * @const integer Status values
     */
    const STATUS_OK = 1;
    const STATUS_ERROR = 99;

    /**
     * @var integer status
     */
    protected $status = 1;

    /**
     * @var array message
     */
    protected $message = array();

    /**
     * @var array html
     */
    protected $html = array();

    /**
     * @var array data
     */
    protected $data = array();

    /**
     * @var array JavaScript
     */
    protected $javaScript = array();

    /**
     * Sets status
     *
     * @param integer $value
     * @return $this
     */
    public function setStatus($value)
    {

        if (defined(get_class($this) . '::' . $value)) {
            $this->status = constant(get_class($this) . '::' . $value);
        } else {
            $this->status = self::STATUS_ERROR;
        }

        return $this;
    }


    /**
     * Sets message
     *
     * @param string  $id
     * @param string  $message
     * @param integer $type
     * @return $this
     */
    public function setMessage($id, $message, $type = 1)
    {

        if (!$message) {
            return $this;
        }

        if (!$this->message[$id]) {
            $this->message[$id] = array();
        }

        $finalType = 99;
        if (in_array(intval($type), array(1, 2, 99))) {
            $finalType = intval($type);
        }

        $this->message[$id]['message'] = $message;
        $this->message[$id]['type'] = $finalType;

        return $this;
    }


    /**
     * Sets data
     *
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Unsets data
     *
     * @return $this
     */
    public function unsetData()
    {
        $this->data = array();
        return $this;
    }


    /**
     * Unset HTML
     *
     * @return $this
     */
    public function unsetHtml()
    {
        $this->html = array();
        return $this;
    }


    /**
     * Sets JavaScript
     *
     * @param boolean $before
     * @param string $javaScript
     * @return $this
     */
    public function setJavaScript($javaScript, $before = false)
    {

        $target = 'after';
        if ($before) {
            $target = 'before';
        }

        if (!is_array($this->javaScript[$target])) {
            $this->javaScript[$target] = array();
        }

        $this->javaScript[$target][] = $javaScript;
        return $this;
    }

    /**
     * Unsets JavaScript
     *
     * @return $this
     */
    public function unsetJavaScript()
    {

        $this->javaScript = array();
        return $this;
    }


    /**
     * Returns JSON-string
     *
     * @return string
     */
    public function __toString()
    {

        $returnArray = array();
        $returnArray['status'] = $this->status;

        if ($this->message) {
            $returnArray['message'] = $this->message;
        }

        if ($this->data) {
            $returnArray['data'] = $this->data;
        }

        if (
            ($this->javaScript)
            && ($this->javaScript['before'])
        ) {
            $returnArray['javaScriptBefore'] = implode(' ', $this->javaScript['before']);
        }

        if ($this->html) {
            $returnArray['html'] = $this->html;
        }

        if (
            ($this->javaScript)
            && ($this->javaScript['after'])
        ) {
            $returnArray['javaScriptAfter'] = implode(' ', $this->javaScript['after']);
        }

        return json_encode($returnArray);
    }

}