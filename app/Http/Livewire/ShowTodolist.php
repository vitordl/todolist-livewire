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
            session()->flash('message', 'You have reached the limit of 300 items in our database');
            return redirect()->to('dashboard');
        }

        $this->validate();

        if($this->editedId != null){
            Log::info('[EDITED]'.now().' - User '.auth()->user()->id. ' with e-mail '.auth()->user()->email.' edited the task '.$this->editedId);

            Todolist::where('id', $this->editedId)->update([
                'content' => $this->task,
                'updated_at' => now()
            ]);
            
            $this->editedId = null;

        }else{

            Log::info('[CREATE]'.now().' - User '.auth()->user()->id. ' with e-mail '.auth()->user()->email.' created a task');

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
            Log::info('[MOVING]'.now().' - User '.auth()->user()->id. ' with e-mail '.auth()->user()->email.' moved a task '.$todolist->status.' TO '.$newStatus);
        }

    }

    public function deleteModal($id){
        $this->deleteId = $id;
        $this->modal = true;
    }

    public function delete(){
        Log::info('[DELETE]'.now().' - User '.auth()->user()->id. ' with e-mail '.auth()->user()->email.' deleted the task '.$this->deleteId);
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
