<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    /**
     * 指定主鍵名稱
     */
    protected $primaryKey = 'report_id';

    /**
     * 指定資料表名稱
     */
    protected $table = 'reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'admin_id',
        'generation_date',
        'report_type',
        'time_period_start',
        'time_period_end',
        'stats_data',
    ];

    /**
     * 不使用 timestamps（根據 schema）
     */
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'generation_date' => 'datetime',
            'time_period_start' => 'datetime',
            'time_period_end' => 'datetime',
            'stats_data' => 'array', // JSON 自動轉換為陣列
        ];
    }

    /**
     * 取得報表的建立者（管理員）
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'admin_id');
    }
}

