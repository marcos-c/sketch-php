<?php
/**
 * This file is part of the Sketch library
 *
 * @author Marcos Cooper <marcos@releasepad.com>
 * @version 2.0.12
 * @copyright 2007 Marcos Cooper
 * @link http://releasepad.com/sketch
 * @package Sketch
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, you can get a copy from the
 * following link: http://opensource.org/licenses/lgpl-2.1.php
 */

namespace Sketch;

class Factory extends Object {
    const QUOTED_IDENTIFIERS = 1;

    /**
     * @var string
     */
    static private $version = null;

    /**
     * @var array
     */
    static private $metadata = null;

    /**
     * @param $table_name
     * @param null $options
     * @return Object
     */
    static function scaffold($table_name, $options = null) {
        try {
            $metadata_table_name = 'metadata';
            if (is_array($options)) {
                if (!array_key_exists('prefix', $options)) $options['prefix'] = 'Abstract';
                if (!array_key_exists('primary_key', $options)) $options['primary_key'] = 'id';
                if (!array_key_exists('generate_iterator', $options)) $options['generate_iterator'] = true;
            } else {
                $options = array('prefix' => 'Abstract', 'primary_key' => 'id', 'generate_iterator' => true);
            }
            $application = Application::getInstance();
            $connection = $application->getConnection();
            $prefix = $connection->getTablePrefix();
            if ($prefix != null) {
                $table_name = "${prefix}_${table_name}";
                $metadata_table_name = "${prefix}_${metadata_table_name}";
            }
            if (!is_array(self::$metadata)) {
                self::$metadata = $connection->getTableDefinition($metadata_table_name);
                self::$version = (self::$metadata['fields']['key'] != null) ? $connection->queryFirst("SELECT value FROM $metadata_table_name WHERE `key` = 'version'") : self::$version;
            }
            if (array_key_exists('class_name', $options)) {
                $class_name = $options['class_name'];
            } else {
                $class_name = null; foreach (explode('[._]', $table_name) as $value) {
                    $class_name .= ucfirst($value);
                }
            }
            if (array_key_exists('namespace', $options)) {
                $namespace = $options['namespace'];
            } else {
                $namespace = 'Common';
            }
            return self::scaffoldFrom(self::$version, $class_name, $namespace, $table_name, $options['prefix'], $options['primary_key'], $options['generate_iterator'], $connection->getTableDefinition($table_name));
        } catch (\Exception $e) {
            exit($e);
        }
    }

    /**
     * @param $version
     * @param string $class_name
     * @param string $namespace
     * @param string $table_name
     * @param $prefix
     * @param string $primary_key
     * @param $generate_iterator
     * @param $table_definition
     * @throws \Exception
     * @return string
     */
    private static function scaffoldFrom($version, $class_name, $namespace, $table_name, $prefix, $primary_key, $generate_iterator, $table_definition) {
        $application = Application::getInstance();
        $translator = $application->getLocale()->getTranslator();
        $signature = md5(serialize(array($version, $class_name, $table_definition['fields'])));
        $filename = $class_name.'_'.substr($signature, 0, 8).".php";
        $document_root = $application->getDocumentRoot();
        if ($document_root != null) {
            $cache_path = dirname($document_root).DIRECTORY_SEPARATOR.'cache';
            $write_path = $cache_path.DIRECTORY_SEPARATOR.'library'.(($version != null) ? DIRECTORY_SEPARATOR.$version : '');
            if (is_readable($write_path.DIRECTORY_SEPARATOR.$filename)) {
                return $write_path.DIRECTORY_SEPARATOR.$filename;
            } else if (is_writable($cache_path)) {
                try {
                    // Generate Class
                    $contents = array();
                    $contents[] = "<?php\n";
                    $contents[] = "/**\n * WARNING! This file was automatically generated!\n */\n\n";
                    $contents[] = "namespace ${namespace};\n\nuse Sketch\\DateTime;\nuse Sketch\\FormView;\nuse Sketch\\ObjectIterator;\nuse Sketch\\ObjectView;\n\n";
                    $contents[] = "abstract class ${prefix}${class_name} extends ObjectView {\n";
                    // Attributes
                    $i = 0; foreach ($table_definition['fields'] as $column => $definition) {
                        if ($column != $primary_key) {
                            $method_name = null; foreach (explode('_', $column) as $value) {
                                $method_name .= ucfirst($value);
                            }
                            $attribute_name = strtolower(substr($method_name, 0, 1)).substr($method_name, 1);
                            $contents[] = (($i++ > 0) ? "\t\n" : "")."\tprivate \$${attribute_name};\n";
                        }
                    }
                    // Constructor
                    if ($table_name != null) {
                        $contents[] = "\t\n";
                        $contents[] = "\tfunction __construct(\$mixed = null) {\n";
                        $contents[] = "\t\tif (!is_array(\$mixed) && \$mixed != null) {\n";
                        $contents[] = sprintf($table_definition['templates']['constructor'], $primary_key);
                        $contents[] = "\t\t}\n\t\tif (!is_array(\$mixed)) \$mixed = array();\n";
                        foreach ($table_definition['fields'] as $column => $definition) {
                            $method_name = null;
                            foreach (explode('_', $column) as $value) {
                                $method_name .= ucfirst($value);
                            }
                            if (preg_match('/^int/', $definition['type']) || preg_match('/^smallint/', $definition['type']) || preg_match('/^tinyint/', $definition['type'])) {
                                $default = intval($definition['default']);
                                $contents[] = ($column == $primary_key) ? "\t\t\$this->setId(array_key_exists('${column}', \$mixed) ? \$mixed['${column}'] : ${default});\n" : "\t\t\$this->set${method_name}(array_key_exists('${column}', \$mixed) ? \$mixed['${column}'] : ${default});\n";
                            } elseif (preg_match('/^bool/', $definition['type']) || preg_match('/^enum\(\'f\',\'t\'|enum\(\'t\',\'f\'/', $definition['type'])) {
                                $default = ($definition['default'] == 't') ? 'true' : 'false';
                                $contents[] = "\t\t\$this->set${method_name}((array_key_exists('${column}', \$mixed) && \$mixed['${column}'] != null) ? \$mixed['${column}'] : ${default});\n";
                            } else {
                                $contents[] = ($column == $primary_key) ? "\t\t\$this->setId(array_key_exists('${column}', \$mixed) ? \$mixed['${column}'] : null);\n" : "\t\t\$this->set${method_name}(array_key_exists('${column}', \$mixed) ? \$mixed['${column}'] : null);\n";
                            }
                        }
                        $contents[] = "\t}\n";
                    }
                    // Getters and Setters
                    foreach ($table_definition['fields'] as $column => $definition) {
                        if ($column != $primary_key) {
                            $method_name = null; foreach (explode('_', $column) as $value) {
                                $method_name .= ucfirst($value);
                            } $attribute_name = strtolower(substr($method_name, 0, 1)).substr($method_name, 1);
                            $contents[] = "\t\n";
                            if (preg_match('/^int/', $definition['type']) || preg_match('/^smallint/', $definition['type']) || preg_match('/^tinyint/', $definition['type'])) {
                                $contents[] = "\tfunction get${method_name}(\$default = false) {\n";
                                $contents[] = "\t\treturn (\$this->${attribute_name} > 0) ? \$this->${attribute_name} : \$default;\n";
                                $contents[] = "\t}\n\t\t\n";
                                $contents[] = "\tfunction set${method_name}(\$${column}) {\n";
                            } else if (!preg_match('/^(date|time)/', $definition['type']) || $definition['null']) {
                                if (preg_match('/^bool/', $definition['type']) || preg_match('/^enum\(\'f\',\'t\'|enum\(\'t\',\'f\'/', $definition['type'])) {
                                    if (preg_match('/^is/', $attribute_name)) {
                                        $contents[] = "\tfunction ${attribute_name}() {\n";
                                    } else {
                                        $contents[] = "\tfunction is${method_name}() {\n";
                                    }
                                    $contents[] = "\t\treturn \$this->${attribute_name};\n";
                                    $contents[] = "\t}\n\t\t\n";
                                }
                                $contents[] = "\tfunction get${method_name}(\$default = null) {\n";
                                $contents[] = "\t\treturn (\$this->${attribute_name} != null) ? \$this->${attribute_name} : \$default;\n";
                                $contents[] = "\t}\n\t\t\n";
                                $contents[] = "\tfunction set${method_name}(\$${column}) {\n";
                            } else {
                                $contents[] = "\t/**\n\t *\n\t * @return DateTime\n\t **/\n\tfunction get${method_name}() {\n";
                                $contents[] = "\t\tif (!(\$this->${attribute_name} instanceof DateTime && \$this->${attribute_name}->isValid())) {\n";
                                if (preg_match('/^(date)/', $definition['type'])) {
                                    $contents[] = "\t\t\t\$this->set${method_name}(DateTime::Today());\n";
                                } else {
                                    $contents[] = "\t\t\t\$this->set${method_name}(DateTime::Now());\n";
                                }
                                $contents[] = "\t\t} return \$this->${attribute_name};\n";
                                $contents[] = "\t}\n\t\t\n";
                                $contents[] = "\t/**\n\t *\n\t * @param DateTime\n\t **/\n\tfunction set${method_name}(\$${column}) {\n";
                            }
                            if (preg_match('/^int/', $definition['type']) || preg_match('/^smallint/', $definition['type']) || preg_match('/^tinyint/', $definition['type'])) {
                                $contents[] = "\t\t\$this->${attribute_name} = intval(\$${column});\n";
                            } else if (preg_match('/^char/', $definition['type']) || preg_match('/^varchar/', $definition['type']) || preg_match('/^text/', $definition['type'])) {
                                $contents[] = "\t\t\$this->${attribute_name} = trim(\$${column});\n";
                            } else if (preg_match('/^bool/', $definition['type']) || preg_match('/^enum\(\'f\',\'t\'|enum\(\'t\',\'f\'/', $definition['type'])) {
                                $contents[] = "\t\t\$this->${attribute_name} = is_bool(\$${column}) ? \$${column} : (\$${column} == 't');\n";
                            } else if (preg_match('/^(date|time)/', $definition['type'])) {
                                $contents[] = "\t\t\$this->${attribute_name} = new DateTime(\$${column});\n";
                            } else {
                                $contents[] = "\t\t\$this->${attribute_name} = \$${column};\n";
                            } $contents[] = "\t}\n";
                        }
                    }
                    // Update and remove action methods
                    $contents[] = "\t\n\tfunction update() {\n\t\t\$connection = \$this->getConnection();\n\t\t\$id = \$this->getId();\n";
                    foreach ($table_definition['fields'] as $column => $definition) {
                        if ($column != $primary_key) {
                            $method_name = null; foreach (explode('_', $column) as $value) {
                                $method_name .= ucfirst($value);
                            }
                            if (preg_match('/^int/', $definition['type']) || preg_match('/^smallint/', $definition['type']) || preg_match('/^tinyint/', $definition['type'])) {
                                $contents[] = "\t\t\$${column} = \$this->get${method_name}(".($definition['null'] ? "'NULL'" : '').");\n";
                            } else if (preg_match('/^bool/', $definition['type']) || preg_match('/^enum\(\'f\',\'t\'|enum\(\'t\',\'f\'/', $definition['type'])) {
                                $contents[] = "\t\t\$${column} = \$this->get${method_name}() ? 't' : 'f';\n";
                            } else if (preg_match('/^(date|time)/', $definition['type']) && $definition['null']) {
                                $contents[] = "\t\t\$${column} = \$this->get${method_name}()->isNull() ? 'NULL' : \"'\".\$this->get${method_name}()->toString().\"'\";\n";
                            } else if (preg_match('/^(date|time)/', $definition['type'])) {
                                $contents[] = "\t\t\$${column} = \$this->get${method_name}()->toString();\n";
                            } else if (preg_match('/^char/', $definition['type']) || preg_match('/^varchar/', $definition['type']) || preg_match('/^text/', $definition['type']) || preg_match('/^time/', $definition['type'])) {
                                if ($definition['null']) {
                                    $contents[] = "\t\t\$${column} = \$connection->escapeString(\$this->get${method_name}());\n";
                                    $contents[] = "\t\t\$${column} = (\$${column} != null) ? \"'\$${column}'\" : 'NULL';\n";
                                } else {
                                    $contents[] = "\t\t\$${column} = \"'\".\$connection->escapeString(\$this->get${method_name}()).\"'\";\n";
                                }
                            } else {
                                $contents[] = "\t\t\$${column} = \$this->get${method_name}();\n";
                                if ($definition['null']) {
                                    $contents[] = "\t\tif (\$${column} == null) \$${column} = 'NULL';\n";
                                }
                            }
                        }
                    }
                    $fields = array('update', 'insert', 'values'); foreach ($table_definition['fields'] as $column => $definition) {
                        if ($column != $primary_key) {
                            $fields['insert'][] = $column;
                            if (preg_match('/^int/', $definition['type']) || preg_match('/^smallint/', $definition['type']) || preg_match('/^tinyint/', $definition['type'])) {
                                $fields['update'][] = "${column} = \$${column}";
                                $fields['values'][] = "\$${column}";
                            } else if (preg_match('/^char/', $definition['type']) || preg_match('/^varchar/', $definition['type']) || preg_match('/^text/', $definition['type'])) {
                                $fields['update'][] = "${column} = \$${column}";
                                $fields['values'][] = "\$${column}";
                            } else if (preg_match('/^bool/', $definition['type'])) {
                                $fields['update'][] = "${column} = '\$${column}'";
                                $fields['values'][] = "'\$${column}'";
                            } else if (preg_match('/^enum\(\'f\',\'t\'|enum\(\'t\',\'f\'/', $definition['type'])) {
                                $fields['update'][] = "${column} = '\$${column}'";
                                $fields['values'][] = "'\$${column}'";
                            } else if (preg_match('/^(date|time)/', $definition['type']) && $definition['null']) {
                                $fields['update'][] = "${column} = \$${column}";
                                $fields['values'][] = "\$${column}";
                            } else if (preg_match('/^(date|time)/', $definition['type'])) {
                                $fields['update'][] = "${column} = '\$${column}'";
                                $fields['values'][] = "'\$${column}'";
                            } else {
                                $fields['update'][] = "${column} = \$${column}";
                                $fields['values'][] = "\$${column}";
                            }
                        }
                    }
                    $contents[] = "\t\tif (\$id) {\n";
                    $contents[] = sprintf($table_definition['templates']['update'], $primary_key, implode(', ', $fields['update']));
                    $contents[] = "\t\t} else {\n";
                    $contents[] = sprintf($table_definition['templates']['insert'], $primary_key, implode(', ', $fields['insert']), implode(', ', $fields['values']));
                    $contents[] = "\t\t}\n";
                    $contents[] = "\t}\n";
                    $contents[] = "\t\n\tfunction updateAction(FormView \$form) {\n\t\t\$validate = method_exists(\$this, 'validate');\n\t\tif (!\$validate || (\$validate && \$this->validate(\$form))) {\n\t\t\treturn \$this->update();\n\t\t} else return false;\n\t}\n";
                    $contents[] = "\t\n\tfunction removeAction(FormView \$form) {\n\t\t\$connection = \$this->getConnection();\n\t\t\$id = \$this->getId();\n\t\tif (\$id) {\n";
                    $contents[] = sprintf($table_definition['templates']['delete'], $primary_key);
                    $contents[] = "\t\t} else return false;\n\t}\n";
                    // Generate Iterator
                    if ($generate_iterator) {
                        $contents[] = "}\n\nclass ${class_name}Iterator extends ObjectIterator {\n";
                        $contents[] = "\tfunction rows() {\n";
                        $contents[] = "\t\tif (\$this->result instanceof ObjectIterator) {\n";
                        $contents[] = "\t\t\treturn \$this->result->rows();\n";
                        $contents[] = "\t\t} else return 0;\n";
                        $contents[] = "\t}\n\t\t\n";
                        $contents[] = "\tfunction fetch(\$key) {\n";
                        $contents[] = "\t\tif (\$this->result instanceof ObjectIterator) {\n";
                        $contents[] = "\t\t\treturn new ${class_name}(\$this->result->fetch(\$key));\n";
                        $contents[] = "\t\t} else return false;\n";
                        $contents[] = "\t}\n\t\t\n";
                        $contents[] = "\tfunction free() {\n";
                        $contents[] = "\t\tif (\$this->result instanceof ObjectIterator) {\n";
                        $contents[] = "\t\t\treturn \$this->result->free();\n";
                        $contents[] = "\t\t}\n";
                        $contents[] = "\t}\n";
                    }
                    $contents[] = "}";
                    if (!file_exists($write_path)) mkdir($write_path);
                    $handle = fopen($write_path.DIRECTORY_SEPARATOR.$filename, 'w');
                    if ($handle) {
                        // Write the generated class
                        foreach ($contents as $line) {
                            fwrite($handle, $line);
                        } fclose($handle);
                    }
                    return $write_path.DIRECTORY_SEPARATOR.$filename;
                } catch (\Exception $e) {
                    throw new \Exception(sprintf($translator->_s("Can't write file %s"), $write_path.DIRECTORY_SEPARATOR.$filename));
                }
            } else {
                throw new \Exception($translator->_s("Cache folder not defined"));
            }
        } else {
            throw new \Exception($translator->_s("Application path not defined"));
        }
    }
}