<?php

namespace App\Http\Livewire;

use App\Models\Todolist;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ShowTodolist extends Component
{

    public $task; 
    public $status = 'todo';
    public $editedId = null;
    public $deleteId;
    public $modal = false;

    public function render()
    {
        
        $todolist = Todolist::orderBy('updated_at', 'desc')->where('user_id', auth()->user()->id)
        ->where('status', $this->status)->latest()->get();

        return view('livewire.show-todolist', ['todolist' => $todolist]);
    }

    public function rules(){
        return [
            'task' => 'required|min:2'
        ];
    }

    public function changeStatus($newStatus){
        $this->status = $newStatus;
    }

    public function cancel(){
        $this->task = '';
        $this->editedId = null;
    }
                                    
    public function create(){
        /* dd($this->texto); */
        if(Todolist::where('user_id', auth()->user()->id)->count() >= 300){
            session()->flash('message', 'Você atingiu o limite de 300 itens no nosso banco de dados!');
            return redirect()->to('dashboard');
        }

        $this->validate();

        if($this->editedId != null){
            Log::info('[EDITED]'.now().' - O usuário '.auth()->user()->id. ' de e-mail '.auth()->user()->email.' editou a task '.$this->editedId);

            Todolist::where('id', $this->editedId)->update([
                'content' => $this->task,
                'updated_at' => now()
            ]);
            
            $this->editedId = null;

        }else{

            Log::info('[CREATE]'.now().' - O usuário '.auth()->user()->id. ' de e-mail '.auth()->user()->email.' criou uma task');

            Todolist::create([
                'content' => $this->task,
                'user_id' => auth()->user()->id
            ]);
       }
        
        $this->task = '';       
    }

    public function updateStatus($newStatus = 'todo', $id =''){
        $todolist = Todolist::where('id', $id)->first();
        Todolist::where('id', $id)->update(['status' => $newStatus]);
        if($todolist->status != $newStatus){
            Log::info('[MOVING]'.now().' - O usuário '.auth()->user()->id. ' de e-mail '.auth()->user()->email.' moveu uma task '.$todolist->status.' para '.$newStatus);
        }

    }

    public function deleteModal($id){
        $this->deleteId = $id;
        $this->modal = true;
    }

    public function delete(){
        Log::info('[DELETE]'.now().' - O usuário '.auth()->user()->id. ' de e-mail '.auth()->user()->email.' deletou a task '.$this->deleteId);
        $todolist = Todolist::findOrFail($this->deleteId);
        $todolist->delete();
        $this->modal = false;
    }

    public function edit($id){
        $todolist = Todolist::findOrFail($id); 
        // dd($todolist->content);
        $this->task = $todolist->content;
        // $this->edited = true;
        $this->editedId = $id;
    
    }


}
