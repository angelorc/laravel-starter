<?php

namespace App\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'first_name', 'last_name', 'email', 'frontend_scheme', 'backend_scheme',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The rules for validation.
     *
     * @var array
     */
    public static $rules = array(
        'user_group_id' => 'required',
        'email' => 'required|email',
        'first_name' => 'required',
        'last_name' => 'required',
        'password' => 'confirmed|min:5',
    );

    /**
     * The rules for validation.
     *
     * @var array
     */
    public function rules()
    {
        return array(
            'email' => 'required|email|unique:users,email,'.$this->id.',id',
            'first_name' => 'required',
            'last_name' => 'required',
            'password' => 'confirmed|min:5',
        );
    }

    /**
     * The rules for validation.
     *
     * @var array
     */
    public function newRules()
    {
        return array(
            'user_group_id' => 'required',
            'email' => 'required|email|unique:users,email,'.$this->id.',id',
            'first_name' => 'required',
            'last_name' => 'required',
            'password' => 'required|confirmed|min:5',
        );
    }

    /**
     * Find item by uid.
     *
     * @return object
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function contact()
    {
        return $this->belongsTo('App\Model\Contact');
    }

    public function user()
    {
        return $this->hasOne('App\Model\User');
    }

    public function userGroup()
    {
        return $this->belongsTo('App\Model\UserGroup');
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (User::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });
    }

    /**
     * Display user name: first_name last_name.
     *
     * @var string
     */
    public function displayName()
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Upload and resize avatar.
     *
     * @var void
     */
    public function uploadImage($file)
    {
        $path = 'app/users/';
        $upload_path = storage_path($path);
        
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        
        $filename = 'avatar-'.$this->id.'.'.$file->getClientOriginalExtension();

        // save to server
        $file->move($upload_path, $filename);

        // create thumbnails
        $img = \Image::make($upload_path.$filename);
        $img->fit(120, 120)->save($upload_path.$filename.'.thumb.jpg');

        return $path.$filename;
    }

    /**
     * Get image thumb path.
     *
     * @var string
     */
    public function imagePath()
    {
        if (!empty($this->image) && !empty($this->id)) {
            return storage_path($this->image).'.thumb.jpg';
        } else {
            return '';
        }
    }

    /**
     * Get image thumb path.
     *
     * @var string
     */
    public function removeImage()
    {
        if (!empty($this->image) && !empty($this->id)) {
            $path = storage_path($this->image);
            if (is_file($path)) {
                unlink($path);
            }
            if (is_file($path.'.thumb.jpg')) {
                unlink($path.'.thumb.jpg');
            }
        }
    }

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $user = $request->user();
        $query = self::select('users.*')
                        ->leftJoin('user_groups', 'user_groups.id', '=', 'users.user_group_id');

        if ($request->user()->getOption('backend', 'user_read') == 'own') {
            $query = $query->where('users.user_id', '=', $request->user()->id);
        }

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('users.first_name', 'like', '%'.$keyword.'%')
                        ->orWhere('users.email', 'like', '%'.$keyword.'%')
                        ->orWhere('user_groups.name', 'like', '%'.$keyword.'%')
                        ->orWhere('users.last_name', 'like', '%'.$keyword.'%');
                });
            }
        }

        // filters
        $filters = $request->filters;
        if (!empty($filters)) {
            if (!empty($filters['user_group_id'])) {
                $query = $query->where('users.user_group_id', '=', $filters['user_group_id']);
            }
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
     * Get user setting.
     *
     * @return string
     */
    public function getOption($cat, $name)
    {
        return $this->userGroup->getOption($cat, $name);
    }

    /**
     * Get user's frontend scheme.
     *
     * @return string
     */
    public function getFrontendScheme()
    {
        if (!empty($this->frontend_scheme)) {
            return $this->frontend_scheme;
        } else {
            return \App\Model\Setting::get('frontend_scheme');
        }
    }

    /**
     * Get user's backend scheme.
     *
     * @return string
     */
    public function getBackendScheme()
    {
        if (!empty($this->backend_scheme)) {
            return $this->backend_scheme;
        } else {
            return \App\Model\Setting::get('backend_scheme');
        }
    }
}
