<?php

namespace App\Livewire;

use App\Services\AnalyticsService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Analytics Dashboard')]
class AnalyticsDashboardComponent extends Component
{
    public $activeTab = 'overview';
    public $dateRange = 30;
    public $refreshInterval = 60; // seconds
    
    // Data properties
    public $overviewData = [];
    public $userAnalytics = [];
    public $marketAnalytics = [];
    public $financialAnalytics = [];
    public $realtimeData = [];
    
    protected AnalyticsService $analyticsService;

    public function boot(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function mount()
    {
        $this->loadAnalyticsData();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->loadAnalyticsData();
    }

    public function setDateRange($days)
    {
        $this->dateRange = $days;
        $this->loadAnalyticsData();
    }

    public function refreshData()
    {
        $this->analyticsService->clearCache();
        $this->loadAnalyticsData();
        
        session()->flash('success', 'Analytics data refreshed successfully!');
    }

    public function loadAnalyticsData()
    {
        try {
            switch ($this->activeTab) {
                case 'overview':
                    $this->overviewData = $this->analyticsService->getPlatformOverview($this->dateRange);
                    $this->realtimeData = $this->analyticsService->getRealTimeDashboard();
                    break;
                    
                case 'users':
                    $this->userAnalytics = $this->analyticsService->getUserAnalytics($this->dateRange);
                    break;
                    
                case 'markets':
                    $this->marketAnalytics = $this->analyticsService->getMarketAnalytics($this->dateRange);
                    break;
                    
                case 'financial':
                    $this->financialAnalytics = $this->analyticsService->getFinancialAnalytics($this->dateRange);
                    break;
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to load analytics data. Please try again.');
            logger()->error('Analytics loading failed', ['error' => $e->getMessage()]);
        }
    }

    public function exportData($format = 'json')
    {
        try {
            $data = $this->analyticsService->exportAnalytics($this->activeTab, $this->dateRange);
            
            $filename = "analytics_{$this->activeTab}_{$this->dateRange}days_" . date('Y-m-d');
            
            if ($format === 'csv') {
                return $this->downloadCsv($data, $filename);
            }
            
            return response()->json($data)
                ->header('Content-Disposition', "attachment; filename=\"{$filename}.json\"");
                
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to export data. Please try again.');
        }
    }

    protected function downloadCsv($data, $filename)
    {
        $csv = fopen('php://temp', 'w+');
        
        // Flatten the data for CSV export
        $flatData = $this->flattenArray($data);
        
        if (!empty($flatData)) {
            // Write headers
            fputcsv($csv, array_keys($flatData[0]));
            
            // Write data
            foreach ($flatData as $row) {
                fputcsv($csv, $row);
            }
        }
        
        rewind($csv);
        $csvContent = stream_get_contents($csv);
        fclose($csv);
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}.csv\"");
    }

    protected function flattenArray($array, $prefix = '')
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            $newKey = $prefix ? $prefix . '.' . $key : $key;
            
            if (is_array($value)) {
                if (isset($value[0]) && is_array($value[0])) {
                    // Handle array of objects/arrays
                    foreach ($value as $index => $item) {
                        if (is_array($item)) {
                            $result = array_merge($result, $this->flattenArray($item, $newKey . '.' . $index));
                        } else {
                            $result[$newKey . '.' . $index] = $item;
                        }
                    }
                } else {
                    $result = array_merge($result, $this->flattenArray($value, $newKey));
                }
            } else {
                $result[$newKey] = $value;
            }
        }
        
        return [$result];
    }

    public function getChartData($type)
    {
        switch ($type) {
            case 'user_registration':
                return $this->userAnalytics['registration_trends'] ?? [];
                
            case 'market_creation':
                return $this->marketAnalytics['market_trends'] ?? [];
                
            case 'volume_trends':
                return $this->financialAnalytics['volume_trends'] ?? [];
                
            case 'markets_by_category':
                return $this->marketAnalytics['markets_by_category'] ?? [];
                
            default:
                return [];
        }
    }

    public function formatCurrency($amount)
    {
        return '₦' . number_format($amount / 100, 2);
    }

    public function formatNumber($number)
    {
        if ($number >= 1000000) {
            return number_format($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return number_format($number / 1000, 1) . 'K';
        }
        
        return number_format($number);
    }

    public function formatPercentage($value, $decimals = 1)
    {
        return number_format($value, $decimals) . '%';
    }

    public function render()
    {
        return view('livewire.analytics-dashboard-component');
    }
}
