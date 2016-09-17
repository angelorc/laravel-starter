<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAll()
    {
        $settings = self::select('*')->get();
        $result = self::defaultSettings();

        foreach ($settings as $setting) {
            $result[$setting->name]['value'] = $setting->value;
        }

        return $result;
    }

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function updateAll()
    {
        $settings = self::getAll();

        foreach ($settings as $key => $setting) {
            self::set($key, $setting['value']);
        }
    }

    /**
     * Get setting.
     *
     * @return object
     */
    public static function get($name)
    {
        $setting = self::where('name', $name)->first();
        if (is_object($setting)) {
            return $setting->value;
        } else {
            return self::defaultSettings()[$name]['value'];
        }
    }

    /**
     * Set setting value.
     *
     * @return object
     */
    public static function set($name, $val)
    {
        $option = self::where('name', $name)->first();

        if (is_object($option)) {
            $option->value = $val;
        } else {
            $option = new self();
            $option->name = $name;
            $option->value = $val;
        }
        $option->save();

        return $option;
    }

    /**
     * Get setting rules.
     *
     * @return object
     */
    public static function rules()
    {
        $rules = [];
        $settings = self::getAll();

        foreach ($settings as $name => $setting) {
            if (!isset($setting['not_required'])) {
                $rules[$name] = 'required';
            }
        }

        return $rules;
    }

    /**
     * Default setting.
     *
     * @return object
     */
    public static function defaultSettings()
    {
        return [
            'site_name' => [
                'cat' => 'general',
                'value' => 'Email Marketing Application',
                'type' => 'text',
            ],
            'site_keyword' => [
                'cat' => 'general',
                'value' => 'Email Marketing, Campaigns, Lists',
                'type' => 'text',
            ],
            'site_online' => [
                'cat' => 'general',
                'value' => true,
                'type' => 'checkbox',
                'options' => [
                    'false', 'true',
                ],
            ],
            'site_offline_message' => [
                'cat' => 'general',
                'value' => 'Application currently offline. We will come back soon!',
                'type' => 'textarea',
            ],
            'site_description' => [
                'cat' => 'general',
                'value' => 'Makes it easy for you to create, send, and optimize your email marketing campaigns.',
                'type' => 'textarea',
            ],
            'frontend_scheme' => [
                'cat' => 'general',
                'value' => 'default',
                'type' => 'select',
                'options' => '',
            ],
            'backend_scheme' => [
                'cat' => 'general',
                'value' => 'default',
                'type' => 'select',
                'options' => '',
            ],
        ];
    }
}
