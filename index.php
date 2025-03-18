<?php
$koneksi = mysqli_connect('localhost','root','','ukk2025_todolist');

//Tambah Task
if (isset($_POST['add_task'])) {
    $task = $_POST['task'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];

    if (!empty($task) && !empty($priority) && !empty($due_date)) {
        mysqli_query($koneksi,"INSERT INTO task VALUES('','$task','$priority','$due_date','0')");
        echo "<script>alert('Data Berhasil Disimpan!');</script>";
    }else{
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

// Menghapus Task
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($koneksi, "DELETE FROM task WHERE id=$id");
    echo "<script>
        alert('Data Berhasil Dihapus!');
        window.location.href='index.php';
    </script>";
}

$result = mysqli_query($koneksi,"SELECT * FROM task ORDER BY status ASC, priority DESC, due_date ASC");
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Aplikasi To do List | UKK PPLG 2025</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
             body { background-color: #eaf6f6; }
            .container { max-width: 1000px; margin-top: 50px; }
            h2 {
                color: #fff;
                background-color: #2e7d67;
                padding: 10px;
                margin: 40px auto 50px auto;
                text-align: center;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            }
            .btn-primary { background-color: #52ab98; border: none; }
            .btn-primary:hover { background-color: #2e7d67; }
            .btn-success { background-color: #76c893; border: none; }
            .btn-danger { background-color: #d9534f; border: none; }
            table { box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); }
            input, select { width: 100%; padding: 10px; margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class="container mt-2">
            <h2 class="text-center">Aplikasi To Do List</h2>
            <form method="POST" class="border rounded bg-light p-2">
                <label class="form-label">Nama Task</label>
                <input type="text" name="task" class="form-control" placeholder="Masukan Task Baru" autocomplete="off" autofocus required>
                <label class="form-label">Prioritas</label>
                <select name="priority" class="form-control" required>
                    <option value="">-- Pilih Prioritas --</option>
                    <option value="1">Low</option>
                    <option value="2">Medium</option>
                    <option value="3">High</option>
                </select>
                <label class="form-label">Tanggal</label>
                <input type="date" name="due_date" class="form-control" value="<?php echo date('Y-m-d') ?>" required>
                <button type="submit" class="btn btn-primary w-100 mt-2" name="add_task">Tambah</button>
            </form>

            <hr>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Task</th>
                        <th>Priority</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (mysqli_num_rows($result) > 0) {
                        $no = 1;
                        while($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $row['task']; ?></td>
                            <td>
                                <?php 
                                if ($row['priority'] == 1) {
                                    echo "Low";
                                }elseif($row['priority'] == 2){
                                    echo "Medium";
                                }else{
                                    echo "High";
                                }
                                ?>
                            </td>
                            <td><?php echo $row['due_date']; ?></td>
                            <td>
                                <?php 
                                if ($row['status'] == 0) {
                                    echo "Belum Selesai";
                                }else{
                                    echo "Selesai";
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if ($row['status'] == 0) { ?>
                                     <a href="?complete=<?php echo $row['id'] ?>" class="btn btn-success">Selesai</a>
                                <?php } ?>
                                <a href="?delete=<?php echo $row['id'] ?>" class="btn btn-danger">Hapus</a>
                            </td>
                        </tr>
                        <?php }
                    }else{
                        echo "Ridak Ada Data";
                    }
                     ?>
                </tbody>
            </table>
        </div>
        
    <scrip src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </body>
</html>