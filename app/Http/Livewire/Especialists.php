<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Especialist;

class Especialists extends Component
{
    use WithPagination;

	protected $paginationTheme = 'bootstrap';
    public $selected_id, $keyWord, $nombre, $apellido, $cedula, $especialidad, $telefono, $fecha_nacimiento, $email;
    public $updateMode = false;

    public function render()
    {
		$keyWord = '%'.$this->keyWord .'%';
        return view('livewire.especialists.view', [
            'especialists' => Especialist::latest()
						->orWhere('nombre', 'LIKE', $keyWord)
						->orWhere('apellido', 'LIKE', $keyWord)
						->orWhere('cedula', 'LIKE', $keyWord)
						->orWhere('especialidad', 'LIKE', $keyWord)
						->orWhere('telefono', 'LIKE', $keyWord)
						->orWhere('fecha_nacimiento', 'LIKE', $keyWord)
						->orWhere('email', 'LIKE', $keyWord)
						->paginate(10),
        ]);
    }
	
    public function cancel()
    {
        $this->resetInput();
        $this->updateMode = false;
    }
	
    private function resetInput()
    {		
		$this->nombre = null;
		$this->apellido = null;
		$this->cedula = null;
		$this->especialidad = null;
		$this->telefono = null;
		$this->fecha_nacimiento = null;
		$this->email = null;
    }

    public function store()
    {
        $this->validate([
		'nombre' => 'required',
		'apellido' => 'required',
		'telefono' => 'required',
        ]);

        Especialist::create([ 
			'nombre' => $this-> nombre,
			'apellido' => $this-> apellido,
			'cedula' => $this-> cedula,
			'especialidad' => $this-> especialidad,
			'telefono' => $this-> telefono,
			'fecha_nacimiento' => $this-> fecha_nacimiento,
			'email' => $this-> email
        ]);
        
        $this->resetInput();
		$this->emit('closeModal');
		session()->flash('message', 'Especialist Successfully created.');
    }

    public function edit($id)
    {
        $record = Especialist::findOrFail($id);

        $this->selected_id = $id; 
		$this->nombre = $record-> nombre;
		$this->apellido = $record-> apellido;
		$this->cedula = $record-> cedula;
		$this->especialidad = $record-> especialidad;
		$this->telefono = $record-> telefono;
		$this->fecha_nacimiento = $record-> fecha_nacimiento;
		$this->email = $record-> email;
		
        $this->updateMode = true;
    }

    public function update()
    {
        $this->validate([
		'nombre' => 'required',
		'apellido' => 'required',
		'telefono' => 'required',
        ]);

        if ($this->selected_id) {
			$record = Especialist::find($this->selected_id);
            $record->update([ 
			'nombre' => $this-> nombre,
			'apellido' => $this-> apellido,
			'cedula' => $this-> cedula,
			'especialidad' => $this-> especialidad,
			'telefono' => $this-> telefono,
			'fecha_nacimiento' => $this-> fecha_nacimiento,
			'email' => $this-> email
            ]);

            $this->resetInput();
            $this->updateMode = false;
			session()->flash('message', 'Especialist Successfully updated.');
        }
    }

    public function destroy($id)
    {
        if ($id) {
            $record = Especialist::where('id', $id);
            $record->delete();
        }
    }
}
