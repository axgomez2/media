<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class MarketAnalysis extends Model
{
    use HasFactory;

    protected $table = 'market_analysis';

    protected $fillable = [
        'analysis_date',
        'total_listings',
        'br_listings',
        'gb_listings',
        'de_listings',
        'us_listings',
        'fr_listings',
        'it_listings',
        'jp_listings',
        'ca_listings',
        'be_listings',
        'se_listings',
    ];

    protected $casts = [
        'analysis_date' => 'date',
        'total_listings' => 'integer',
        'br_listings' => 'integer',
        'gb_listings' => 'integer',
        'de_listings' => 'integer',
        'us_listings' => 'integer',
        'fr_listings' => 'integer',
        'it_listings' => 'integer',
        'jp_listings' => 'integer',
        'ca_listings' => 'integer',
        'be_listings' => 'integer',
        'se_listings' => 'integer',
    ];

    /**
     * Buscar análises por período
     */
    public static function getByDateRange(Carbon $startDate, Carbon $endDate)
    {
        return self::whereBetween('analysis_date', [$startDate, $endDate])
                   ->orderBy('analysis_date', 'desc')
                   ->get();
    }

    /**
     * Buscar análise mais recente
     */
    public static function getLatest()
    {
        return self::orderBy('analysis_date', 'desc')->first();
    }

    /**
     * Buscar análises dos últimos N dias
     */
    public static function getLastDays(int $days)
    {
        return self::where('analysis_date', '>=', Carbon::now()->subDays($days))
                   ->orderBy('analysis_date', 'desc')
                   ->get();
    }

    /**
     * Verificar se já existe análise para uma data específica
     */
    public static function existsForDate(Carbon $date)
    {
        return self::where('analysis_date', $date->format('Y-m-d'))->exists();
    }

    /**
     * Obter dados para gráfico de evolução por país
     */
    public static function getCountryEvolution(int $days = 30)
    {
        return self::select('analysis_date', 'br_listings', 'gb_listings', 'de_listings', 'us_listings', 'fr_listings')
                   ->where('analysis_date', '>=', Carbon::now()->subDays($days))
                   ->orderBy('analysis_date', 'asc')
                   ->get();
    }

    /**
     * Obter dados para gráfico de volume de listagens
     */
    public static function getListingsEvolution(int $days = 30)
    {
        return self::select('analysis_date', 'total_listings', 'br_listings')
                   ->where('analysis_date', '>=', Carbon::now()->subDays($days))
                   ->orderBy('analysis_date', 'asc')
                   ->get();
    }

    /**
     * Obter estatísticas resumidas
     */
    public static function getSummaryStats()
    {
        $latest = self::getLatest();

        if (!$latest) {
            return [
                'current' => null,
                'previous' => null,
                'growth' => null,
            ];
        }

        $previous = self::where('analysis_date', '<', $latest->analysis_date)
                         ->orderBy('analysis_date', 'desc')
                         ->first();

        if (!$previous) {
            return [
                'current' => $latest,
                'previous' => null,
                'growth' => null,
            ];
        }

        return [
            'current' => $latest,
            'previous' => $previous,
            'growth' => [
                'total_listings' => self::calculateGrowth($previous->total_listings, $latest->total_listings),
                'br_listings' => self::calculateGrowth($previous->br_listings, $latest->br_listings),
            ],
        ];
    }

    /**
     * Calcular crescimento percentual
     */
    private static function calculateGrowth($previous, $current)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
