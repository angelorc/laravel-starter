<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'backend_access', 'frontend_access', 'options',
    ];

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAll()
    {
        return self::select('*');
    }

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

    /**
     * The rules for validation.
     *
     * @var array
     */
    public static $rules = array(
        'name' => 'required',
    );

    /**
     * Rules.
     *
     * @return array
     */
    public static function rules()
    {
        $rules = [
            'name' => 'required',
        ];

        $options = self::defaultOptions();
        foreach ($options as $type => $option) {
            foreach ($option as $name => $value) {
                $rules['options.'.$type.'.'.$name] = 'required';
            }
        }

        return $rules;
    }

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function users()
    {
        return $this->hasMany('App\Model\User');
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $user = $request->user();
        $query = self::select('user_groups.*');

        if ($request->user()->getOption('backend', 'user_group_read') == 'own') {
            $query = $query->where('user_id', '=', $request->user()->id);
        }

        // Keyword
        if (!empty(trim($request->keyword))) {
            $query = $query->where('name', 'like', '%'.$request->keyword.'%');
        }

        return $query;
    }

    /**
     * Search items.
     *
     * @return collect
     */
    public static function search($request)
    {
        $query = self::filter($request);

        $query = $query->orderBy($request->sort_order, $request->sort_direction);

        return $query;
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Update custom order
            UserGroup::getAll()->increment('custom_order', 1);
            $item->custom_order = 0;
        });
    }

    /**
     * Get select options.
     *
     * @return array
     */
    public static function getSelectOptions()
    {
        $options = self::getAll()->get()->map(function ($item) {
            return ['value' => $item->id, 'text' => $item->name];
        });

        return $options;
    }

    /**
     * Default options for new groups.
     *
     * @return array
     */
    public static function defaultOptions()
    {
        return [
            'frontend' => [

            ],
            'backend' => [
                
            ],
        ];
    }

    /**
     * Backend roles.
     *
     * @return array
     */
    public static function backendPermissions()
    {
        return [
            
        ];
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions()
    {
        if (empty($this->options)) {
            return self::defaultOptions();
        } else {
            $defaul_options = self::defaultOptions();
            $saved_options = json_decode($this->options, true);
            foreach($defaul_options as $x => $group) {
                foreach($group as $y => $option) {
                    if(isset($saved_options[$x][$y])) {
                        $defaul_options[$x][$y] = $saved_options[$x][$y];
                    }
                }
            }
            return $defaul_options;
        }
    }

    /**
     * Get option.
     *
     * @return string
     */
    public function getOption($cat, $name)
    {
        return $this->getOptions()[$cat][$name];
    }

    /**
     * Save options.
     *
     * @return array
     */
    public function saveOptions($options)
    {
        return true;
    }
}
