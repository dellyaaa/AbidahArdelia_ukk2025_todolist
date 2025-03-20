<?php
//Koneksi ke db
$koneksi = mysqli_connect('localhost','root','','ukk2025_todolist');

// Tambah kolom deskripsi jika belum ada
$check_column = mysqli_query($koneksi, "SHOW COLUMNS FROM task LIKE 'deskripsi'");
if(mysqli_num_rows($check_column) == 0) {
    mysqli_query($koneksi, "ALTER TABLE task ADD COLUMN deskripsi TEXT AFTER task");
}

//Tambah Task
if (isset($_POST['add_task'])) { // Cek apakah tombol tambah diklik
    $task = $_POST['task'];
    $deskripsi = $_POST['deskripsi'];
    $priority = $_POST['priority']; 
    $due_date = $_POST['due_date'];
    $today = date('Y-m-d');

    if (!empty($task) && !empty($priority) && !empty($due_date)) {
        // Validasi tanggal tidak boleh di masa lalu
        if($due_date < $today) {
            echo "<script>alert('Tanggal tidak boleh di masa lalu!');</script>";
        } else {
            // Query untuk memasukkan data task ke database
            mysqli_query($koneksi,"INSERT INTO task VALUES('','$task','$deskripsi','$priority','$due_date','0')");
            echo "<script>alert('Data Berhasil Disimpan!');</script>";
        }
    } else {
        echo "<script>alert('Semua Kolom Harus Diisi!');</script>";
    }
}

// Menandai Task Selesai
if (isset($_GET['complete'])) {
    $id = $_GET['complete'];
    mysqli_query($koneksi, "UPDATE task SET status=1 WHERE id=$id");
    echo "<script>
        alert('Data Berhasil Diupdate!');
        window.location.href='index.php';
    </script>";
}

// Undo Task Selesai
if (isset($_GET['undo'])) {
    $id = $_GET['undo'];
    mysqli_query($koneksi, "UPDATE task SET status=0 WHERE id=$id");
    echo "<script>
        alert('Task dikembalikan ke status belum selesai!');
        window.location.href='index.php';
    </script>";
}

// Menghapus Task
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($koneksi, "DELETE FROM task WHERE id=$id");
    echo "<script>
        alert('Data Berhasil Dihapus!');
        window.location.href='index.php';
    </script>";
}

// Update Task
if (isset($_POST['update_task'])) {
    $id = $_POST['id'];
    $task = $_POST['task'];
    $deskripsi = $_POST['deskripsi'];
    $priority = $_POST['priority'];
    
    mysqli_query($koneksi, "UPDATE task SET task='$task', deskripsi='$deskripsi', priority='$priority' WHERE id=$id");
    echo "<script>
        alert('Data Berhasil Diupdate!');
        window.location.href='index.php';
    </script>";
}

// Set default priority filter
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query untuk menampilkan data task dengan filter
$query = "SELECT * FROM task WHERE 1=1";

// Tambahkan filter prioritas
if (!empty($priority_filter)) {
    $query .= " AND priority='$priority_filter'";
}

// Tambahkan pencarian
if (!empty($search)) {
    $query .= " AND (task LIKE '%$search%' OR deskripsi LIKE '%$search%')";
}

// Order by status, priority dan tanggal
$query .= " ORDER BY status ASC, priority DESC, due_date ASC";

// Pagination setup
$limit = 5; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Get total records for pagination
$total_records_query = str_replace("SELECT *", "SELECT COUNT(*) as total", $query);
$total_result = mysqli_query($koneksi, $total_records_query);
$total_records = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_records / $limit);

// Add limit to the main query
$query .= " LIMIT $start, $limit";

// Execute final query
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html>
    <head>
        <!-- Setting dasar halaman -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Aplikasi To do List | UKK PPLG 2025</title>

        <!-- Memuat Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome untuk ikon -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link href="style.css" rel="stylesheet">
    </head>
    <body>
        <div class="container-fluid mt-4">
            <div class="app-container">
                <h2 class="text-center app-title">Aplikasi To Do List</h2> 
                
                <div class="row">
                    <!-- Kolom Input - Sebelah Kiri -->
                    <div class="col-md-4">
                        <div class="input-section">
                            <h4 class="section-title">Tambah Task Baru</h4>
                            <form method="POST" class="task-form" id="taskForm">
                                <div class="mb-3">
                                    <label class="form-label">Nama Task</label>
                                    <input type="text" name="task" class="form-control" placeholder="Masukkan Task Baru" autocomplete="off" autofocus required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control" placeholder="Masukkan deskripsi task" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Prioritas</label>
                                    <select name="priority" class="form-control" required>
                                        <option value="">-- Pilih Prioritas --</option>
                                        <option value="1">Low</option>
                                        <option value="2">Medium</option>
                                        <option value="3">High</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" name="due_date" id="due_date" class="form-control" value="<?php echo date('Y-m-d') ?>" min="<?php echo date('Y-m-d') ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100" name="add_task">Tambah Task</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Kolom Tabel - Sebelah Kanan -->
                    <div class="col-md-8">
                        <div class="task-table-section">
                            <h4 class="section-title">Daftar Task</h4>
                            
                            <!-- Search Bar dan Filter -->
                            <div class="search-filter-container mb-3">
                                <form method="GET" class="d-flex justify-content-between align-items-center">
                                    <div class="search-group">
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control" placeholder="Cari task..." value="<?php echo $search; ?>">
                                            <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
                                        </div>
                                    </div>
                                    <div class="filter-group">
                                        <select name="priority" class="form-select" onchange="this.form.submit()">
                                            <option value="">Semua Prioritas</option>
                                            <option value="1" <?php if($priority_filter == '1') echo 'selected'; ?>>Low</option>
                                            <option value="2" <?php if($priority_filter == '2') echo 'selected'; ?>>Medium</option>
                                            <option value="3" <?php if($priority_filter == '3') echo 'selected'; ?>>High</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Tabel menampilkan daftar task -->
                            <div class="task-list">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Task</th>
                                            <th>Deskripsi</th>
                                            <th>Priority</th>
                                            <th>Tanggal</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if (mysqli_num_rows($result) > 0) {
                                            $no = $start + 1;
                                            while($row = mysqli_fetch_assoc($result)) { ?>
                                            <tr class="<?php echo $row['status'] == 1 ? 'completed-task' : ''; ?>">
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $row['task']; ?></td>
                                                <td>
                                                    <?php 
                                                    if(!empty($row['deskripsi'])) {
                                                        echo $row['deskripsi']; 
                                                    } else {
                                                        echo "<em>Tidak ada deskripsi</em>";
                                                    }
                                                    ?>
                                                </td>

                                                <!-- Menampilkan prioritas berdasarkan angka -->
                                                <td>
                                                    <span class="priority-badge priority-<?php echo $row['priority']; ?>">
                                                    <?php 
                                                    if ($row['priority'] == 1) {
                                                        echo "Low";
                                                    } elseif($row['priority'] == 2) {
                                                        echo "Medium";
                                                    } else {
                                                        echo "High";
                                                    }
                                                    ?>
                                                    </span>
                                                </td>

                                                <!-- Menampilkan tanggal task -->
                                                <td><?php echo date('d M Y', strtotime($row['due_date'])); ?></td>

                                                <!-- Menampilkan status selesai atau belum -->
                                                <td>
                                                    <span class="status-badge status-<?php echo $row['status']; ?>">
                                                    <?php 
                                                    if ($row['status'] == 0) {
                                                        echo "Belum Selesai";
                                                    } else {
                                                        echo "Selesai";
                                                    }
                                                    ?>
                                                    </span>
                                                </td>

                                                <!-- Tombol aksi dengan ikon -->
                                                <td class="action-buttons">
                                                    <?php if ($row['status'] == 0) { ?>
                                                        <a href="?complete=<?php echo $row['id'] ?>" class="btn btn-sm btn-success" title="Tandai Selesai">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>" title="Edit Task">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    <?php } else { ?>
                                                        <a href="?undo=<?php echo $row['id'] ?>" class="btn btn-sm btn-warning" title="Batalkan Selesai">
                                                            <i class="fas fa-undo"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <a href="?delete=<?php echo $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus task ini?')" title="Hapus Task">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>

                                            <!-- Modal Edit Task -->
                                            <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editModalLabel">Edit Task</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Nama Task</label>
                                                                    <input type="text" name="task" class="form-control" value="<?php echo $row['task']; ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Deskripsi</label>
                                                                    <textarea name="deskripsi" class="form-control" rows="3"><?php echo $row['deskripsi']; ?></textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Prioritas</label>
                                                                    <select name="priority" class="form-control" required>
                                                                        <option value="1" <?php if($row['priority'] == 1) echo 'selected'; ?>>Low</option>
                                                                        <option value="2" <?php if($row['priority'] == 2) echo 'selected'; ?>>Medium</option>
                                                                        <option value="3" <?php if($row['priority'] == 3) echo 'selected'; ?>>High</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Tanggal (Tidak dapat diubah)</label>
                                                                    <input type="date" class="form-control" value="<?php echo $row['due_date']; ?>" disabled>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" name="update_task" class="btn btn-primary">Simpan Perubahan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php }
                                        } else { ?>
                                            <tr>
                                                <td colspan="7" class="text-center">Tidak Ada Data</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-3">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php if($page <= 1){ echo 'disabled'; } ?>">
                                        <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo $search; ?>&priority=<?php echo $priority_filter; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php if($page == $i){ echo 'active'; } ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&priority=<?php echo $priority_filter; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php if($page >= $total_pages){ echo 'disabled'; } ?>">
                                        <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo $search; ?>&priority=<?php echo $priority_filter; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Memuat Bootstrap JS -->    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script untuk validasi tanggal -->
    <script>
        // Mendapatkan tanggal hari ini dalam format YYYY-MM-DD
        function getTodayDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // Validasi form sebelum submit
        document.getElementById('taskForm').addEventListener('submit', function(event) {
            const dueDateInput = document.getElementById('due_date');
            const dueDate = dueDateInput.value;
            const today = getTodayDate();
            
            if (dueDate < today) {
                event.preventDefault();
                alert('Tanggal tidak boleh di masa lalu!');
                dueDateInput.value = today;
            }
        });

        // Tambahkan validasi saat mengubah nilai input
        document.getElementById('due_date').addEventListener('change', function() {
            const dueDate = this.value;
            const today = getTodayDate();
            
            if (dueDate < today) {
                alert('Tanggal tidak boleh di masa lalu!');
                this.value = today;
            }
        });
    </script>
    </body>
</html>