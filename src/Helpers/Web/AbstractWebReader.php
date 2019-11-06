<?php

namespace App\Helpers\Web;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractWebReader implements WebReaderInterface
{
    protected $extra = [];

    public $source;
    public $searchPatterns;
    public $newLineDelimiter = '<br>';
    public $explodePattern = "\n";

    /**
     * @var HttpClientInterface
     */
    protected $httpClient;

    protected $content;

    protected $_explodedData;

    public function __construct(array $config)
    {
        foreach ($config as $name => $value) {
            $setter = 'set' . ucfirst($name);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
                unset($config[$name]);
            } elseif (property_exists($this, $name)) {
                $this->$name = $value;
                unset($config[$name]);
            }
        }
        $this->extra = $config;
        $this->httpClient = HttpClient::create();
        $this->explodePattern = str_replace('\n', $this->newLineDelimiter, $this->explodePattern);
    }

    abstract protected function getData(string $source, string $method): string;

    public function read(string $source, string $method = "GET"): array
    {
        if (empty($this->content = $this->getData($source, $method))) {
            return [];
        }
        if (empty($this->searchPatterns)) {
            return [$this->content];
        }
        $this->prepareContent();
        return $this->filterData();
    }

    protected function prepareContent()
    {
        $this->content = preg_replace('~[\r\n]+~', $this->newLineDelimiter, $this->content);
        $this->content = preg_replace('~[\t ]+~', ' ', $this->content);
    }

    protected function explodeData(): array
    {
        if ($this->_explodedData !== null) {
            return $this->_explodedData;
        }
        return $this->_explodedData = array_filter(preg_split("/{$this->explodePattern}/", $this->content));
    }

    protected function filterData(): array
    {
        $searchPatterns = $this->searchPatterns;
        $data = [];
        while (!empty($searchPatterns)) {
            $data = array_merge($data, $this->runAllSearchPatterns($searchPatterns));
        }
        $this->_explodedData = null;
        return $data;
    }

    protected function runAllSearchPatterns(array &$searchPatterns)
    {
        if (empty($searchPatterns)) {
            return [];
        }
        $firstPattern = $secondPattern = $paramName = $searchPattern = $replaceFrom = $replaceTo = $explode = $multiple = null;

        $data = [];
        foreach ($this->explodeData() as $i => $string) {
            if (empty($string = trim($string))) {
                continue;
            }

            foreach ($searchPatterns as $param => $patternsList) {
                if (!isset($firstPattern) || !isset($secondPattern)) {
                    list($firstPattern, $secondPattern, $replaceFrom, $replaceTo, $explode, $multiple) = $patternsList;
                }
                if (preg_match('/' . $firstPattern . '/', $string)) {
                    $paramName = $param;
                    $searchPattern = $secondPattern;
                    unset($searchPatterns[$param]);
                    break;
                }
            }

            if (!empty($paramName) && !empty($searchPattern)) {
                $results = [];
                if (preg_match_all('/' . $searchPattern . '/U', $string, $matches) && is_array($matches) && count($matches) === 2) {
                    foreach ($matches[1] as $res) {
                        if ($replaceFrom !== null && $replaceTo !== null) {
                            $res = str_replace($replaceFrom, $replaceTo, $res);
                        }
                        $res = $explode !== null ? explode($explode, $res) : [$res];
                        $results = array_merge($results, $res);
                    }
                    if (!$multiple && !empty($results)) {
                        $results = $results[0];
                    }
                    $data[$paramName] = $results;
                    $firstPattern = $secondPattern = $paramName = $searchPattern = $replaceFrom = $replaceTo = $explode = $multiple = null;
                }
            }
        }
        foreach ($searchPatterns as $firsIndex => $firstValue) {
            $data[$firsIndex] = isset($firstValue[5]) && $firstValue[5] === true ? [] : null;
            unset($searchPatterns[$firsIndex]);
            break;
        }

        return $data;
    }
}