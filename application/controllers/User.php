<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

   public function __construct() {
      parent::__construct();
      $this->load->model('Main_model');

      if ($this->session->userdata('akses') != 2) {
         redirect('dashboard_administrator');
      }

      if ($this->session->userdata('email') != TRUE) {
         redirect('auth/login_user');
      } 
   }

   public function index() {
      $data['title'] = "Dashboard User";
      $data['user']  = $this->db->get_where('tbl_user', ["email" => $this->session->userdata('email')])->row_array();
      
      $this->load->view('component/header', $data);
      $this->load->view('component/sidebar', $data);
      $this->load->view('user/dashboard', $data);
      $this->load->view('component/footer');
    }

    public function edit_user($id_user) {
      $data = [ 
         "title" => "Dashboard",
         "edit" => "Edit Mahasiswa",
         "user" => $this->db->get_where("tbl_user", ['email' => $this->session->userdata('email')])->row_array(),
         "get_user" => $this->Main_model->get_user($id_user)->result()
      ];
      
      $this->load->view('component/header', $data);
      $this->load->view('component/sidebar', $data);
      $this->load->view('user/ubah-password', $data);
      $this->load->view('component/footer');
   }

   public function update_user() {
      $this->form_validation->set_rules('nama', 'Input Nama Lengkap', 'required');

      if ($this->form_validation->run() == FALSE) {
         $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert"><div class="alert alert-success alert-dismissible fade show" role="alert">Data gagal diedit!<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
         redirect('dashboard');  
      } else {

         $nama = $this->input->post('nama');
         $email = $this->input->post('email');

         // cek jika ada gambar yang akan diupload
            $upload_image = $_FILES['gambar']['name'];

            if ($upload_image) {
                $config['allowed_types'] = 'gif|jpg|png';
                $config['max_size']      = '2048';
                $config['upload_path'] = './vendor/image/';

                $this->load->library('upload', $config);

                if ($this->upload->do_upload('gambar')) {
                    $old_image = $data['tbl_user']['gambar'];
                    if ($old_image != 'user.png') {
                        unlink(FCPATH . './vendor/image/' . $old_image);
                    }
                    $new_image = $this->upload->data('file_name');
                    $this->db->set('gambar', $new_image);
                } else {
                    echo $this->upload->dispay_errors();
                }
            }

            $this->db->set('nama', $nama);
            $this->db->where('email', $email);
            $this->db->update('tbl_user');

            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible fade show" role="alert">Data berhasil di update!<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
            redirect('dashboard');
      }
   } 

   public function matkul($id_user) {
      $data = [ 
         "title" => "Matakuliah",
         "matkul" => "Ambil Matakuliah",
         "user" => $this->db->get_where("tbl_user", ['email' => $this->session->userdata('email')])->row_array(),
         "get_matkul" => $this->Main_model->get_matkul($id_user)->result_array(),
         "get_user" => $this->Main_model->get_user($id_user)->result()
      ];
      
      $this->load->view('component/header', $data);
      $this->load->view('component/sidebar', $data);
      $this->load->view('user/matakuliah', $data);
      $this->load->view('component/footer');
   }

   public function ambil_matkul() {
      
      $id_matkul = $this->input->post('id_matkul');
      $id_user = $this->input->post('id_user');
      $matkul = $this->input->post('matkul');

         $data = [
            'id_kelas_matkul'=> $id_matkul,
            'id_user'=> $id_user,
            'matkul'  => $matkul,
            'status'   => 1
         ];
        $success = $this->db->insert('tbl_kelas_matkul', $data);

        if ($success) {
         $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible fade show" role="alert">Data berhasil ditambahkan<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
         redirect('dashboard');
        } else {
         $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible fade show" role="alert">Data gagal ditambahkan, pastikan Matakuliah belum diambil!<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');   
         redirect('dashboard');     
      }
   }

   public function delete_matkul($id_user) {
      
      $data = [ 
         "title" => "Matakuliah",
         "matkul" => "Hapus Matakuliah",
         "user" => $this->db->get_where("tbl_user", ['email' => $this->session->userdata('email')])->row_array(),
         "matkul_user" => $this->Main_model->matkul_user($id_user)->result_array()
      ];
      
      $this->load->view('component/header', $data);
      $this->load->view('component/sidebar', $data);
      $this->load->view('user/hapus-matkul', $data);
      $this->load->view('component/footer');
   }

   public function hapus_matkul() {
      $matkul = $this->input->post('matkul');
      $jumlah_dipilih = count($matkul);
      for ($i = 0; $i < $jumlah_dipilih; $i++) {
         $this->Main_model->delete_matkul($matkul[$i]);
      }
      $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible fade show" role="alert">Matakuliah berhasil dihapus!<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');   
      redirect('dashboard');
   }
}

 