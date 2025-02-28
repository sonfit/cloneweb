<?php

namespace App\Livewire;

use App\Filament\Resources\TongHopResource;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;
use Filament\Forms\Form;

class PublicTongHopForm extends Component implements HasForms
{
    use InteractsWithForms;

    public $data = [];

// Định nghĩa form với type hint chính xác
    public function form(Form $form): Form
    {
        // Lấy schema trực tiếp từ TongHopResource::form()
        $resourceForm = TongHopResource::form($form);
        $schema = $resourceForm->getComponents(); // Lấy mảng các thành phần form
        return $form
            ->schema($schema) // Truyền schema vào đây
            ->statePath('data');
    }
    public function mount()
    {
        $this->form->fill(); // Điền dữ liệu mặc định (nếu có)
    }

    public function submit()
    {
        $data = $this->form->getState(); // Lấy dữ liệu từ form
        // Xử lý dữ liệu, ví dụ: lưu vào database
        // TongHop::create($data);
        session()->flash('message', 'Dữ liệu đã được gửi thành công!');
    }

    public function render()
    {
        return view('livewire.public-tong-hop-form')
            ->layout('layouts.public');
    }
}
