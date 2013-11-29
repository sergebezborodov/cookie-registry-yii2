<?php

namespace sergebezborodov\cookie;

use yii\base\Component;
use yii\web\Cookie;

/**
 * Implementation of registry pattern for cookies
 * Main idea - all cookies configuration must be defined in one place
 *
 * @property array $cookies
 * @package sergebezborodov\cookie
 */
class CookieRegistry extends Component implements \ArrayAccess
{
    /**
     * @var array
     */
    private $_cookiesConfig;

    /**
     * Use md5 hash for cookie name
     *
     * @var bool
     */
    public $hashName = false;

    /**
     * Salt for hash
     *
     * @var string
     */
    public $hashSalt = 'wazzup';


    const NAME      = 'name';
    const VALUE     = 'value';
    const DOMAIN    = 'domain';
    const EXPIRE    = 'expire';
    const HTTP_ONLY = 'httpOnly';
    const PATH      = 'path';
    const SECURE    = 'secure';


    /**
     * Validate cookie
     *
     * @param string $alias
     */
    protected function validateCookie($alias)
    {
        if (!array_key_exists($alias, $this->_cookiesConfig)) {
            throw new Exception("Cookie with alias '{$alias}' doesn't found");
        }
        $cookieConfig = $this->_cookiesConfig[$alias];

        if (empty($cookieConfig[self::NAME])) {
            throw new Exception("Cookie with alias '{$alias}' doesn't have name");
        }
    }

    /**
     * Return cookie real name by alias
     *
     * @param string $alias
     * @return string
     */
    protected function getCookieNameByAlias($alias)
    {
        $name =  $this->_cookiesConfig[$alias][self::NAME];
        return $this->hashName ? $this->hashName($name) : $name;
    }

    /**
     * Hash cookie name
     *
     * @param string $name
     * @return string
     */
    protected function hashName($name)
    {
        return md5($this->hashSalt.'-'.$name);
    }

    /**
     * Find cookie by alias and return it actual value
     *
     * @param string $alias
     * @return string
     */
    public function getCookie($alias)
    {
        $this->validateCookie($alias);

        /** @var Cookie $cookie */
        if ($cookie = \Yii::$app->getResponse()->getCookies()->get($this->getCookieNameByAlias($alias))) {
            return $cookie ? $cookie->value : null;
        } else {
            $cookie = \Yii::$app->getRequest()->getCookies()->get($this->getCookieNameByAlias($alias));
            return $cookie ? $cookie->value : null;
        }
    }

    /**
     * Sets cookie value by it alias
     *
     * @param string $alias
     * @param mixed $value
     */
    public function setCookie($alias, $value)
    {
        $this->validateCookie($alias);
        $cookie = new Cookie([
            self::NAME  => $this->getCookieNameByAlias($alias),
            self::VALUE => $value
        ]);
        $cookieConfig = $this->_cookiesConfig[$alias];

        $attrs = array(self::DOMAIN, self::HTTP_ONLY, self::PATH, self::SECURE);
        foreach ($attrs as $attr) {
            if (array_key_exists($attr, $cookieConfig)) {
                $cookie->$attr = $cookieConfig[$attr];
            }
        }
        if (array_key_exists(self::EXPIRE, $cookieConfig)) {
            $cookie->expire = strtotime($cookieConfig[self::EXPIRE]);
        }

        \Yii::$app->getResponse()->getCookies()->add($cookie);
    }

    /**
     * Deletes cookie by alias
     * It's sets new cookie with life in past
     *
     * @param string $alias
     */
    public function removeCookie($alias)
    {
        $this->validateCookie($alias);
        \Yii::$app->getResponse()->getCookies()->remove($this->getCookieNameByAlias($alias));
    }

    /**
     * Setter for config
     *
     * @param array $config
     */
    public function setCookies($config)
    {
        $this->_cookiesConfig = $config;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return $this->getCookie($offset) != null;
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->getCookie($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->setCookie($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        $this->removeCookie($offset);
    }
}