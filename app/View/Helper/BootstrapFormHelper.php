<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 *
 * Licensed under The MIT License
 *
 * Copyright (c) La Pâtisserie, Inc. (http://patisserie.keensoftware.com/)
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('FormHelper', 'View/Helper');
class BootstrapFormHelper extends FormHelper
{

    /**
     * Default input values with bootstrap classes
     * Changed order of error and after to be able to display validation error messages inline
     */
    protected $_inputDefaults = array(
        'format' => array('before', 'label', 'between', 'input', 'error', 'after'),
        'div' => 'form-group',
        'label' => array('class' => 'control-label col-sm-2'),
        'between' => '<div class="col-sm-10">',
        'after' => '</div>',
        'class' => 'form-control',
        'error' => array('attributes' => array('wrap' => 'span', 'class' => 'help-inline text-danger'))
    );

    /**
     * Added an array_merge_recursive for labels to combine $_inputDefaults with specific view markup for labels like custom text.
     * Also removed null array for options existing in $_inputDefaults.
     */
    protected function _parseOptions($options)
    {
        if (!empty($options['label'])) {
            //manage case 'label' => 'your label' as well as 'label' => array('text' => 'your label') before array_merge()
            if (!is_array($options['label'])) {
                $options['label'] = array('text' => $options['label']);
            }
            $options['label'] = array_merge_recursive($options['label'], $this->_inputDefaults['label']);
        }

        $options = array_merge(
            array('before' => null),
            $this->_inputDefaults,
            $options
        );
        return parent::_parseOptions($options);
    }

    /**
     * adds the default class 'form-horizontal to the <form>
     *
     */
    public function create($model = null, $options = array())
    {
        if (isset($this->request['language']) && !empty($options['url'])) {
            if (is_array($options['url'])) {
                $options['url']['language'] = Configure::read('Config.langCode');
            } else {
                $options['url'] = '/' . $this->request['language'] . $options['url'];
            }
        }

        $class = array(
            'class' => 'form-horizontal',
        );
        $options = array_merge($class, $options);
        return parent::create($model, $options);
    }

    /**
     * modified the first condition with a more general empty() otherwise if $default is an empty array
     * !is_null() returns true and $this->_inputDefaults is erased
     */
    public function inputDefaults($defaults = null, $merge = false)
    {
        if (!empty($defaults)) {
            if ($merge) {
                $this->_inputDefaults = array_merge($this->_inputDefaults, (array)$defaults);
            } else {
                $this->_inputDefaults = (array)$defaults;
            }
        }
        return $this->_inputDefaults;
    }

    /**
     * Create a bootstrap help text for the input
     *
     * @param array $basicOptions Options by default for the input
     * @param string $value       Text
     * @return array
     */
    public function __addInputhelp($options, $value) {
        $options['after'] = '<span class="help-block">' . $value . '</span>' . $options['after'];
        return $options;
    }

    public function input($fieldName, $options = array()) {
        $this->setEntity($fieldName);
        $options = $this->_parseOptions($options);

        $divOptions = $this->_divOptions($options);
        $bootstrapOptions = array('help');
        foreach ($bootstrapOptions as $opt) {
            if (isset($options[$opt])) {
                $name = '__addInput' . $opt;
                if(gettype($name)=='string')
                $options = $this->$name($options, $options[$opt]);
            }
        }

        unset($options['div']);

        if ($options['type'] === 'radio' && isset($options['options'])) {
            $radioOptions = (array)$options['options'];
            unset($options['options']);
        }

        $label = $this->_getLabel($fieldName, $options);
        if ($options['type'] !== 'radio') {
            unset($options['label']);
        }

        $error = $this->_extractOption('error', $options, null);
        unset($options['error']);

        $errorMessage = $this->_extractOption('errorMessage', $options, true);
        unset($options['errorMessage']);

        $selected = $this->_extractOption('selected', $options, null);
        unset($options['selected']);

        if ($options['type'] === 'datetime' || $options['type'] === 'date' || $options['type'] === 'time') {
            $dateFormat = $this->_extractOption('dateFormat', $options, 'MDY');
            $timeFormat = $this->_extractOption('timeFormat', $options, 12);
            unset($options['dateFormat'], $options['timeFormat']);
        }

        $type = $options['type'];
        $out = array('before' => $options['before'], 'label' => $label, 'between' => $options['between'], 'after' => $options['after']);
        $format = $this->_getFormat($options);

        unset($options['type'], $options['before'], $options['between'], $options['after'], $options['format']);

        $out['error'] = null;
        if ($type !== 'hidden' && $error !== false) {
            $errMsg = $this->error($fieldName, $error);
            if ($errMsg) {
                $divOptions = $this->addClass($divOptions, 'has-error');
                if ($errorMessage) {
                    $out['error'] = $errMsg;
                }
            }
        }

        if ($type === 'radio' && isset($out['between'])) {
            $options['between'] = $out['between'];
            $out['between'] = null;
        }
        $out['input'] = $this->_getInput(compact('type', 'fieldName', 'options', 'radioOptions', 'selected', 'dateFormat', 'timeFormat'));

        $output = '';
        foreach ($format as $element) {
            $output .= $out[$element];
        }

        if (!empty($divOptions['tag'])) {
            $tag = $divOptions['tag'];
            unset($divOptions['tag']);
            $output = $this->Html->tag($tag, $output, $divOptions);
        }
        return $output;
    }

    public function submit($caption = null, $options = array()) {
        if (!is_string($caption) && empty($caption)) {
            $caption = __d('cake', 'Submit');
        }
        $out = null;
        $div = true;


        if (isset($options['div'])) {
            $div = $options['div'];
            unset($options['div']);
        }
/*
        format' => array('before', 'label', 'between', 'input', 'error', 'after'),
        'div' => 'form-group',
        'label' => array('class' => 'control-label col-sm-2'),
        'between' => '<div class="col-sm-10">',
        'after' => '</div>',
        'class' => 'form-control',
        'error' => array('attributes' => array('wrap' => 'span', 'class' => 'help-inline text-danger'))
*/

        $options += array('type' => 'submit', 'class'=>'btn btn-primary', 'before'=>'<div class="col-sm-4 col-sm-offset-2">', 'after' => '</div>', 'secure' => false);
        $divOptions = array('tag' => 'div');

        if ($div === true) {
            $divOptions['class'] = 'form-group';
        } elseif ($div === false) {
            unset($divOptions);
        } elseif (is_string($div)) {
            $divOptions['class'] = $div;
        } elseif (is_array($div)) {
            $divOptions = array_merge(array('class' => 'submit', 'tag' => 'div'), $div);
        }

        if (isset($options['name'])) {
            $name = str_replace(array('[', ']'), array('.', ''), $options['name']);
            $this->_secure($options['secure'], $name);
        }
        unset($options['secure']);

        $before = $options['before'];
        $after = $options['after'];
        unset($options['before'], $options['after']);

        $isUrl = strpos($caption, '://') !== false;
        $isImage = preg_match('/\.(jpg|jpe|jpeg|gif|png|ico)$/', $caption);

        if ($isUrl || $isImage) {
            $unlockFields = array('x', 'y');
            if (isset($options['name'])) {
                $unlockFields = array(
                    $options['name'] . '_x', $options['name'] . '_y'
                );
            }
            foreach ($unlockFields as $ignore) {
                $this->unlockField($ignore);
            }
        }

        if ($isUrl) {
            unset($options['type']);
            $tag = $this->Html->useTag('submitimage', $caption, $options);
        } elseif ($isImage) {
            unset($options['type']);
            if ($caption{0} !== '/') {
                $url = $this->webroot(Configure::read('App.imageBaseUrl') . $caption);
            } else {
                $url = $this->webroot(trim($caption, '/'));
            }
            $url = $this->assetTimestamp($url);
            $tag = $this->Html->useTag('submitimage', $url, $options);
        } else {
            $options['value'] = $caption;
            $tag = $this->Html->useTag('submit', $options);
        }
        $out = $before . $tag . $after;

        if (isset($divOptions)) {
            $tag = $divOptions['tag'];
            unset($divOptions['tag']);
            $out = $this->Html->tag($tag, $out, $divOptions);
        }
        return $out;
    }

}