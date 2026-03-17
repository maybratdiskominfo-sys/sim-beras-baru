<?php

namespace App\Livewire\Front;

use App\Models\Post;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class Home extends Component
{
    use WithPagination;

    // Menambahkan Atribut #[Url] agar filter tersimpan di URL (bisa di-share/copy link)
    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $category = '';

    #[Url(history: true)]
    public $departmentId = '';

    protected $paginationTheme = 'bootstrap';

    // Lifecycle hook untuk reset halaman jika filter berubah
    public function updatedSearch() { $this->resetPage(); }
    public function updatedCategory() { $this->resetPage(); }
    public function updatedDepartmentId() { $this->resetPage(); }

    /**
     * Fitur Reset Filter
     */
    public function resetFilters()
    {
        $this->reset(['search', 'category', 'departmentId']);
        $this->resetPage();
    }

    public function render()
    {
        // Query dengan Eager Loading untuk efisiensi memori (N+1 Query fix)
        $query = Post::query()
            ->with(['user', 'department'])
            ->where('status', 'published');

        // Logic Filter Reaktif
        $query->when($this->search, function($q) {
            $q->where('title', 'like', '%' . $this->search . '%');
        });

        $query->when($this->category, function($q) {
            $q->where('category', $this->category);
        });

        $query->when($this->departmentId, function($q) {
            $q->where('department_id', $this->departmentId);
        });

        return view('livewire.front.home', [
            'posts' => $query->latest()->paginate(6), // Menggunakan 5 atau 6 per halaman
            
            // Ambil data untuk komponen dropdown di Blade
            'departments' => Department::select('id', 'name')->get(),
            
            // Mengambil kategori unik yang benar-benar memiliki postingan 'published'
            'categories' => Post::where('status', 'published')
                                ->whereNotNull('category')
                                ->distinct()
                                ->pluck('category')
        ]);
    }
}