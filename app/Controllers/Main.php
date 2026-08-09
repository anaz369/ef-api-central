<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\File;

class Main extends BaseController
{
    protected $db;
    protected $model;
    protected $session;
    protected $data = [];
    public function __construct(){
        $this->db = db_connect();
        $this->model= new File;
        $this->session = session();
        $this->data['session']= $this->session ;
        $this->data['request'] = $this->request ;
        $this->data['uploads']= $this->model->findAll();
    }
    public function index(){
        return view('home',$this->data);
    }

    public function upload(){
        if(!is_dir('./uploads/'))
        mkdir('./uploads/');
        $label = $this->request->getPost('label');
        $file = $this->request->getFile('file');
        $fname = $file->getRandomName();
        while(true){
            $check = $this->model->where("path", "uploads/{$fname}")->countAllResults();
            if($check > 0){
                $fname = $file->getRandomName();
            }else{
                break;
            }
        }
        $data=[
            "label" =>$this->db->escapeString($label),
            "path" => "uploads/".$fname
        ];
        if($file->move("uploads/", $fname)){
            $this->model->save($data);
            $this->session->setFlashdata('main_success',"New File Uploaded successfully.");
            return redirect()->to('/');
        }else{
            $this->session->setFlashdata('main_success',"File Upload failed.");
        }
            return view('home', $this->data);
    }

    public function upload2(){
        if(!is_dir('./uploads/'))
        mkdir('./uploads/');
        $label = $this->request->getPost('label');
        $file = $this->request->getFile('file');
        $fname = $file->getRandomName();
        while(true){
            $check = $this->model->where("path", "uploads/{$fname}")->countAllResults();
            if($check > 0){
                $fname = $file->getRandomName();
            }else{
                break;
            }
        }
        $data=[
            "label" =>$this->db->escapeString($label),
            "path" => "uploads/".$fname
        ];
        if($file->move("uploads/", $fname)){
            $this->model->save($data);
            $this->session->setFlashdata('main_success',"New File Uploaded successfully.");
            return redirect()->to('/');
        }else{
            $this->session->setFlashdata('main_success',"File Upload failed.");
        }
            return view('home', $this->data);
    }
}
