<?php

namespace Peak\Service\Category;

use Illuminate\Database\Eloquent\Builder;

class Core extends \Illuminate\Database\Eloquent\Model
{

	protected $table = '9peak_category';
	public $timestamps = false;

	protected $fillable = [
		'name' => 'name',
		'intro' => 'intro',
		'img' => 'img',
		'pid' => 'pid',
		'top' => 'top',
		'status' => 'status',
	];

	protected $hidden = [
		'type'
	];

	protected $casts = [
		'top' => 'int'
	];


	protected static function boot()
	{
		parent::boot();

		static::addGlobalScope('type', function (Builder $builder) {
			defined('static::TYPE') && $builder->where('type', static::TYPE);
		});

		static::saving(function ($model) {
			defined('static::TYPE') && $model->type = static::TYPE;
		});

		static::saved(function ($model){
			if (!$model->pid) {
				$model->pid = $model->id;
				$model->save();
			}
		});

	}




	/**
	 * 是否是顶级分类
	 * */
	public function isTopest ()
	{
		return $this->id==$this->pid;
	}



	### type 设置

//	static function type ()
//	{
//
//	}


	### 作用域查询


	/**
	 * 搜索顶级栏目
	 * @param $query
	 * @param $type float 类型编号
	 * */
	public function scopeWhereType ($query, $type)
	{
		return $query->where('type', $type);
	}



	/**
	 * 搜索顶级栏目
	 * @param $query
	 * @param $top boolean true检索顶级栏目，false检索非顶级栏目
	 * */
	public function scopeWhereTopest ($query, $top=true)
	{
		return $query->whereRaw( (boolean)$top ? 'pid=id' : 'pid!=id');
	}


	/**
	 * 检索状态
	 * @param $query
	 * @param $status int|array 状态值
	 * */
	public function scopeWhereStatus ($query, $status)
	{
		return is_array($status) ? $query->whereIn('status', $status) : $query->where('status', $status);
	}


	/**
	 * 搜索名称
	 * */
	public function scopeWhereName ($query, $name, $like=false)
	{
		return $like ? $query->where('name', 'like', '%'.$name.'%') : $query->where('name', $name);
	}


	# model方法



	### Model 关系

	/**
	 * 下级分类
	 * */
	public function sub ()
	{
		return $this->hasMany(static::class, 'pid', 'id')->whereRaw('pid!=id');
	}


	/**
	 * 上级分类
	 * */
	public function sup ()
	{
		return $this->belongsTo(static::class, 'id', 'pid')->whereRaw('id!=pid');
	}

}
