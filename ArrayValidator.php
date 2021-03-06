<?php

/*
 * Copyright (c) 2011-2015 Lp digital system
 *
 * This file is part of BackBee.
 *
 * BackBee is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * BackBee is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with BackBee. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Charles Rouillon <charles.rouillon@lp-digital.fr>
 */

namespace BackBee\Validator;

/**
 * ArrayValidator's validator
 *
 * @category    BackBee
 * @package     BackBee\Validator
 * @copyright   Lp digital system
 * @author      f.kroockmann <florian.kroockmann@lp-digital.fr>
 */
class ArrayValidator extends AbstractValidator
{
    const DELIMITER = '__';

    /**
     * Validate all datas with config
     *
     * @param  array  $array
     * @param  array  $datas
     * @param  array  $errors
     * @param  array  $form_config
     * @param  string $prefix
     * @return array
     */
    public function validate($array, array $datas = array(), array &$errors = array(), array $form_config = array(), $prefix = '')
    {
        foreach ($datas as $key => $data) {
            if (null !== $cConfig = $this->getData($key, $form_config)) {

                if ($set_empty = isset($cConfig[self::CONFIG_PARAMETER_SET_EMPTY])) {
                    $set_empty =  true === $cConfig[self::CONFIG_PARAMETER_SET_EMPTY];
                }

                $do_treatment = true;
                if (isset($cConfig[self::CONFIG_PARAMETER_MANDATORY])) {
                    if (false === $cConfig[self::CONFIG_PARAMETER_MANDATORY] && empty($data) && !$set_empty) {
                        $do_treatment = false;
                    }
                }

                if (true === $do_treatment) {
                    if (true === isset($cConfig[self::CONFIG_PARAMETER_VALIDATOR])) {

                        $do_treatment = true;
                        if (true === empty($data) && true === $set_empty) {
                            $do_treatment = false;
                        }

                        if (true === $do_treatment) {
                            foreach ($cConfig[self::CONFIG_PARAMETER_VALIDATOR] as $validator => $validator_conf) {
                                $this->doGeneralValidator($data, $key, $validator, $validator_conf, $errors);
                            }
                        }
                    }

                    $do_set = true;
                    if (false === $set_empty && true === empty($data)) {
                        $do_set = false;
                    }

                    if (true === $do_set) {
                        $this->setData($key, $data, $array);
                    }
                }
            }
        }

        return $array;
    }

    /**
     * Get data to array
     *
     * @param  string      $key
     * @param  array       $array
     * @return null|string
     */
    public function getData($key, $array)
    {
        $matches = explode(self::DELIMITER, $key);
        if (count($matches) > 0) {
            foreach ($matches as $match) {
                if (true === isset($array[$match])) {
                    $array = $array[$match];
                } else {
                    $array = null;
                    break;
                }
            }
        }

        return $array;
    }

    /**
     * Set data to array
     *
     * @param string $key
     * @param string $value
     * @param array  $array
     */
    public function setData($key, $value, &$array)
    {
        $matches = explode(self::DELIMITER, $key);

        $target = &$array;
        foreach ($matches as $index) {
            if (false === array_key_exists($index, $array)) {
                throw new \InvalidArgumentException(sprintf('Index %s not found in array', $index));
            }
            $target = &$target[$index];
            if (false === is_array($target)) {
                break;
            }
        }

        $target = $value;
    }
}
