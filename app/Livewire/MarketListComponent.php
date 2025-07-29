<?php

namespace App\Livewire;

use App\Models\Market;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class MarketListComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $statusFilter = 'open';
    public $sortBy = 'closes_at';
    public $sortDirection = 'asc';
    public $perPage = 12;

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'statusFilter' => ['except' => 'open'],
        'sortBy' => ['except' => 'closes_at'],
        'sortDirection' => ['except' => 'asc'],
    ];

    /**
     * Reset pagination when search or filters change
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    /**
     * Sort markets by column
     */
    public function sortBy($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Clear all filters
     */
    public function clearFilters()
    {
        $this->search = '';
        $this->categoryFilter = '';
        $this->statusFilter = 'open';
        $this->sortBy = 'closes_at';
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    /**
     * Listen for market updates
     */
    #[On('market-updated')]
    public function refreshMarkets()
    {
        // This will refresh the component when markets are updated
    }

    /**
     * Get markets with filters and pagination
     */
    public function getMarketsProperty()
    {
        $query = Market::with(['category', 'stakes'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('question', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter, function ($query) {
                $query->where('category_id', $this->categoryFilter);
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'closing-soon') {
                    $query->where('status', 'open')
                          ->where('closes_at', '<=', now()->addHours(24));
                } elseif ($this->statusFilter === 'trending') {
                    // Markets with high activity (most stakes in last 24 hours)
                    $query->where('status', 'open')
                          ->withCount(['stakes' => function ($q) {
                              $q->where('created_at', '>=', now()->subDay());
                          }])
                          ->orderBy('stakes_count', 'desc');
                } else {
                    $query->where('status', $this->statusFilter);
                }
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    /**
     * Get categories for filter dropdown
     */
    public function getCategoriesProperty()
    {
        return Category::orderBy('name')->get();
    }

    /**
     * Calculate odds for a market side
     */
    public function calculateOdds($market, $side)
    {
        $totalYesStakes = $market->stakes()->where('side', 'yes')->sum('amount');
        $totalNoStakes = $market->stakes()->where('side', 'no')->sum('amount');
        $totalStakes = $totalYesStakes + $totalNoStakes;

        if ($totalStakes === 0) {
            return 1.50; // Default odds when no stakes
        }

        if ($side === 'yes') {
            $probability = $totalYesStakes / $totalStakes;
        } else {
            $probability = $totalNoStakes / $totalStakes;
        }

        // Prevent division by zero and ensure minimum odds
        $probability = max(0.01, min(0.99, $probability));
        $odds = 1 / $probability;

        return round($odds, 2);
    }

    public function render()
    {
        return view('livewire.market-list-component', [
            'markets' => $this->markets,
            'categories' => $this->categories,
        ]);
    }
}
