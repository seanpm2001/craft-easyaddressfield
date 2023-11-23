<?php

namespace studioespresso\easyaddressfield\web\twig\variables;

use Craft;
use craft\helpers\Template;
use studioespresso\easyaddressfield\assetbundles\easyaddressmap\EasyAddressMapAsset;
use studioespresso\easyaddressfield\EasyAddressField;
use studioespresso\easyaddressfield\models\EasyAddressFieldModel;
use studioespresso\easyaddressfield\services\CountriesService;

class AddressVariable
{
    private $key;

    private $settings;

    public function __construct()
    {
        $pluginSettings = EasyAddressField::getInstance()->getSettings();
        $this->settings = $pluginSettings;
        $this->key = $pluginSettings->googleApiKeyNonGeo ? $pluginSettings->googleApiKeyNonGeo : $pluginSettings->googleApiKey;
    }

    public function countries()
    {
        $countriesService = new CountriesService();

        return $countriesService->getCountriesAsArray();
    }

    /**
     * @param $data :  array of EasyAddressField Model
     * @param int $zoom : Zoom level of the map
     * @param string $size : size of the rendered image, maximum 640x640
     * @param string $style : map image style, if defined, it overrules the style defined in settings*
     * @param string $color : HEX color value
     * @param string $icon : url to the custom icon
     * @param int $scale: Scale level of the image
     *
     * @return bool|string
     * @throws \craft\errors\DeprecationException
     *
     */
    public function getStaticMap($data, $zoom = 14, $size = '640x640', $style = null, $color = null, $icon = null, $scale = 1)
    {
        Craft::$app->getDeprecator()->log(__CLASS__ . 'getStaticMap', "The 'getStaticMap' method will be remove in Craft 5.");
        $image = $this->getStaticMapRaw($data, $zoom, $size, $style, $color, $icon, $scale);

        return '<img src="' . $image . '"></a>';

    }

    /**
     * @param $data :  array of EasyAddressField Model
     * @param int $zoom : Zoom level of the map
     * @param string $size : size of the rendered image, maximum 640x640
     * @param string $style : map image style, if defined, it overrules the style defined in settings*
     * @param string $color : HEX color value
     * @param string $icon : url to the custom icon
     * @param int $scale: Scale level of the image
     *
     * @return bool|string
     * @throws \craft\errors\DeprecationException
     */
    public function getStaticMapRaw($data, $zoom = 14, $size = '640x640', $style = null, $color = null, $icon = null, $scale = 1)
    {
        Craft::$app->getDeprecator()->log(__CLASS__ . 'getStaticMapRaw', "The 'getStaticMapRaw' method will be remove in Craft 5.");

        if (!$this->key) {
            return false;
        }

        // Support a custom marker color for each tag or fall back to the color set in settings, or the default color
        if (isset($color) && preg_match('/^#[a-f0-9]{6}$/i', $color)) {
            $markerColor = '0x' . ltrim($color, '#');
        } else {
            $markerColor = $this->settings->defaultMarkerColor ? '0x' . ltrim($this->settings->defaultMarkerColor,
                    '#') : 'red';
        }

        // Check if the tag has a custom marker set or if we have a default custom maker
        if (isset($icon) || $this->settings->defaultMarkerIcon) {
            $markerIcon = $icon ? $icon : $this->settings->defaultMarkerIcon;
        } else {
            $markerIcon = false;
        }

        // Use the custom marker if we have it, otherwise fall back to the standard marker + possible custom colors
        if($markerIcon) {
            $markerProperties = "icon:" . $markerIcon. '|';
        } else {
            $markerProperties = "color:" . $markerColor . '|';
        }

        $baseLink = 'https://maps.googleapis.com/maps/api/staticmap?';
        $params = array(
            'zoom' => $zoom,
            'size' => $size,
            'scale' => $scale,
            'maptype' => 'roadmap',
            'key' => Craft::parseEnv($this->key),
            'style' => $this->getMapStyle($style),
        );
        if (!is_array($data)) {
            $data = [$data];
        }

        $location = '';
        foreach ($data as $address) {
            $lat = $address['latitude'];
            $lng = $address['longitude'];
            $location .= '&markers=' . $markerProperties. $lat . ',' . $lng;
        }
        $image = $baseLink . http_build_query($params) . $location;

        return urldecode($image);
    }

    /**
     * @param $data
     * @param $currentLocation
     *
     * @return string
     * @throws \craft\errors\DeprecationException
     */
    public function getDirectionsUrl( $addressModel, $currentLocation = false ) {
        Craft::$app->getDeprecator()->log(__CLASS__ . 'getDirectionsUrl', "The 'getDirectionsUrl' has been moved to a function on the field, instead of through a twig variable. The previous way of working will be removed in Craft 5");
        if ( $currentLocation ) {
            $str = 'https://www.google.com/maps/dir/current+location/';
        } else {
            $str = 'https://www.google.com/maps/dir//';
        }
        $data         = $addressModel->toString(',');
        $str          .= str_replace( ' ', '+', $data );

        return $str;
    }

    private function getMapStyle($style)
    {
        if (!$style) {
            $style = $this->settings->defaultMapTheme;
        }
        switch ($style) {
            case 'silver':
                $style = 'element:geometry%7Ccolor:0xf5f5f5&style=element:labels.icon%7Cvisibility:off&style=element:labels.text.fill%7Ccolor:0x616161&style=element:labels.text.stroke%7Ccolor:0xf5f5f5&style=feature:administrative.land_parcel%7Celement:labels.text.fill%7Ccolor:0xbdbdbd&style=feature:poi%7Celement:geometry%7Ccolor:0xeeeeee&style=feature:poi%7Celement:labels.text.fill%7Ccolor:0x757575&style=feature:poi.park%7Celement:geometry%7Ccolor:0xe5e5e5&style=feature:poi.park%7Celement:labels.text.fill%7Ccolor:0x9e9e9e&style=feature:road%7Celement:geometry%7Ccolor:0xffffff&style=feature:road.arterial%7Celement:labels.text.fill%7Ccolor:0x757575&style=feature:road.highway%7Celement:geometry%7Ccolor:0xdadada&style=feature:road.highway%7Celement:labels.text.fill%7Ccolor:0x616161&style=feature:road.local%7Celement:labels.text.fill%7Ccolor:0x9e9e9e&style=feature:transit.line%7Celement:geometry%7Ccolor:0xe5e5e5&style=feature:transit.station%7Celement:geometry%7Ccolor:0xeeeeee&style=feature:water%7Celement:geometry%7Ccolor:0xc9c9c9&style=feature:water%7Celement:labels.text.fill%7Ccolor:0x9e9e9e';
                break;
            case 'retro':
                $style = 'element:geometry%7Ccolor:0xebe3cd&style=element:labels.text.fill%7Ccolor:0x523735&style=element:labels.text.stroke%7Ccolor:0xf5f1e6&style=feature:administrative%7Celement:geometry.stroke%7Ccolor:0xc9b2a6&style=feature:administrative.land_parcel%7Celement:geometry.stroke%7Ccolor:0xdcd2be&style=feature:administrative.land_parcel%7Celement:labels.text.fill%7Ccolor:0xae9e90&style=feature:landscape.natural%7Celement:geometry%7Ccolor:0xdfd2ae&style=feature:poi%7Celement:geometry%7Ccolor:0xdfd2ae&style=feature:poi%7Celement:labels.text.fill%7Ccolor:0x93817c&style=feature:poi.park%7Celement:geometry.fill%7Ccolor:0xa5b076&style=feature:poi.park%7Celement:labels.text.fill%7Ccolor:0x447530&style=feature:road%7Celement:geometry%7Ccolor:0xf5f1e6&style=feature:road.arterial%7Celement:geometry%7Ccolor:0xfdfcf8&style=feature:road.highway%7Celement:geometry%7Ccolor:0xf8c967&style=feature:road.highway%7Celement:geometry.stroke%7Ccolor:0xe9bc62&style=feature:road.highway.controlled_access%7Celement:geometry%7Ccolor:0xe98d58&style=feature:road.highway.controlled_access%7Celement:geometry.stroke%7Ccolor:0xdb8555&style=feature:road.local%7Celement:labels.text.fill%7Ccolor:0x806b63&style=feature:transit.line%7Celement:geometry%7Ccolor:0xdfd2ae&style=feature:transit.line%7Celement:labels.text.fill%7Ccolor:0x8f7d77&style=feature:transit.line%7Celement:labels.text.stroke%7Ccolor:0xebe3cd&style=feature:transit.station%7Celement:geometry%7Ccolor:0xdfd2ae&style=feature:water%7Celement:geometry.fill%7Ccolor:0xb9d3c2&style=feature:water%7Celement:labels.text.fill%7Ccolor:0x92998d';
                break;
            case 'dark':
                $style = 'element:geometry%7Ccolor:0x212121&style=element:labels.icon%7Cvisibility:off&style=element:labels.text.fill%7Ccolor:0x757575&style=element:labels.text.stroke%7Ccolor:0x212121&style=feature:administrative%7Celement:geometry%7Ccolor:0x757575&style=feature:administrative.country%7Celement:labels.text.fill%7Ccolor:0x9e9e9e&style=feature:administrative.land_parcel%7Cvisibility:off&style=feature:administrative.locality%7Celement:labels.text.fill%7Ccolor:0xbdbdbd&style=feature:poi%7Celement:labels.text.fill%7Ccolor:0x757575&style=feature:poi.park%7Celement:geometry%7Ccolor:0x181818&style=feature:poi.park%7Celement:labels.text.fill%7Ccolor:0x616161&style=feature:poi.park%7Celement:labels.text.stroke%7Ccolor:0x1b1b1b&style=feature:road%7Celement:geometry.fill%7Ccolor:0x2c2c2c&style=feature:road%7Celement:labels.text.fill%7Ccolor:0x8a8a8a&style=feature:road.arterial%7Celement:geometry%7Ccolor:0x373737&style=feature:road.highway%7Celement:geometry%7Ccolor:0x3c3c3c&style=feature:road.highway.controlled_access%7Celement:geometry%7Ccolor:0x4e4e4e&style=feature:road.local%7Celement:labels.text.fill%7Ccolor:0x616161&style=feature:transit%7Celement:labels.text.fill%7Ccolor:0x757575&style=feature:water%7Celement:geometry%7Ccolor:0x000000&style=feature:water%7Celement:labels.text.fill%7Ccolor:0x3d3d3d';
                break;
            case 'night':
                $style = 'element:geometry%7Ccolor:0x242f3e&style=element:labels.text.fill%7Ccolor:0x746855&style=element:labels.text.stroke%7Ccolor:0x242f3e&style=feature:administrative.locality%7Celement:labels.text.fill%7Ccolor:0xd59563&style=feature:poi%7Celement:labels.text.fill%7Ccolor:0xd59563&style=feature:poi.park%7Celement:geometry%7Ccolor:0x263c3f&style=feature:poi.park%7Celement:labels.text.fill%7Ccolor:0x6b9a76&style=feature:road%7Celement:geometry%7Ccolor:0x38414e&style=feature:road%7Celement:geometry.stroke%7Ccolor:0x212a37&style=feature:road%7Celement:labels.text.fill%7Ccolor:0x9ca5b3&style=feature:road.highway%7Celement:geometry%7Ccolor:0x746855&style=feature:road.highway%7Celement:geometry.stroke%7Ccolor:0x1f2835&style=feature:road.highway%7Celement:labels.text.fill%7Ccolor:0xf3d19c&style=feature:transit%7Celement:geometry%7Ccolor:0x2f3948&style=feature:transit.station%7Celement:labels.text.fill%7Ccolor:0xd59563&style=feature:water%7Celement:geometry%7Ccolor:0x17263c&style=feature:water%7Celement:labels.text.fill%7Ccolor:0x515c6d&style=feature:water%7Celement:labels.text.stroke%7Ccolor:0x17263c';
                break;
            case 'aubergine':
                $style = 'element:geometry%7Ccolor:0x1d2c4d&style=element:labels.text.fill%7Ccolor:0x8ec3b9&style=element:labels.text.stroke%7Ccolor:0x1a3646&style=feature:administrative.country%7Celement:geometry.stroke%7Ccolor:0x4b6878&style=feature:administrative.land_parcel%7Celement:labels.text.fill%7Ccolor:0x64779e&style=feature:administrative.province%7Celement:geometry.stroke%7Ccolor:0x4b6878&style=feature:landscape.man_made%7Celement:geometry.stroke%7Ccolor:0x334e87&style=feature:landscape.natural%7Celement:geometry%7Ccolor:0x023e58&style=feature:poi%7Celement:geometry%7Ccolor:0x283d6a&style=feature:poi%7Celement:labels.text.fill%7Ccolor:0x6f9ba5&style=feature:poi%7Celement:labels.text.stroke%7Ccolor:0x1d2c4d&style=feature:poi.park%7Celement:geometry.fill%7Ccolor:0x023e58&style=feature:poi.park%7Celement:labels.text.fill%7Ccolor:0x3C7680&style=feature:road%7Celement:geometry%7Ccolor:0x304a7d&style=feature:road%7Celement:labels.text.fill%7Ccolor:0x98a5be&style=feature:road%7Celement:labels.text.stroke%7Ccolor:0x1d2c4d&style=feature:road.highway%7Celement:geometry%7Ccolor:0x2c6675&style=feature:road.highway%7Celement:geometry.stroke%7Ccolor:0x255763&style=feature:road.highway%7Celement:labels.text.fill%7Ccolor:0xb0d5ce&style=feature:road.highway%7Celement:labels.text.stroke%7Ccolor:0x023e58&style=feature:transit%7Celement:labels.text.fill%7Ccolor:0x98a5be&style=feature:transit%7Celement:labels.text.stroke%7Ccolor:0x1d2c4d&style=feature:transit.line%7Celement:geometry.fill%7Ccolor:0x283d6a&style=feature:transit.station%7Celement:geometry%7Ccolor:0x3a4762&style=feature:water%7Celement:geometry%7Ccolor:0x0e1626&style=feature:water%7Celement:labels.text.fill%7Ccolor:0x4e6d70';
                break;
            default:
                $style = '';
                break;
        };

        return $style;

    }

}