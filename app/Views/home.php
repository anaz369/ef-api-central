<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeIgniter File Upload</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" integrity="sha512-6PM0qYu5KExuNcKt5bURAoT6KCThUmHRewN3zUFNaoI6Di7XJPTMoT6K0nsagZKk2OB4L7E3q1uQKHNHd4stIQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style>
        html, body{
            height:100%;
            width:100%;
        }
        button#user-topnav-menu {
            background: #fff;
            border: 1px solid #b1b1b1;
            padding: 0.2em 2em;
            text-align: left !important;
            border-radius: 2em;
        }
        @media print{
            .col-lg-6,.col-md-6{
                width:50%;
            }
            .lh-1{
                line-height:1em;
            }
        }
    </style>
</head>
<body class="bg-dark bg-gradient bg-opacity-25">
    <div class="container py-4">
            <h1 class="text-center fw-bold">File Uploading in CodeIgniter 4</h1>
            <hr>
            <?php if($session->getFlashdata('main_error')): ?>
                <div class="alert alert-danger rounded-0">
                    <?= $session->getFlashdata('main_error') ?>
                </div>
            <?php endif; ?>
            <?php if($session->getFlashdata('main_success')): ?>
                <div class="alert alert-success rounded-0">
                    <?= $session->getFlashdata('main_success') ?>
                </div>
            <?php endif; ?>
            <div class="clear-fix py-2"></div>
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                    <div class="card rounded-0 shadow">
                        <div class="card-header">
                            <div class="card-title h4 mb-0 fw-bold">Upload File Form</div>
                        </div>
                        <div class="card-body">
                            <div class="container-fluid">
                                <form action="<?= base_url('file/upload') ?>" method="POST" id="file-upload" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="label" class="control-label">File Label</label>
                                        <input type="text" name="label" id="label" class="form-control rounded-0">
                                    </div>
                                    <div class="mb-3">
                                        <label for="formFile" class="form-label">File</label>
                                        <input class="form-control rounded-0" name="file" type="file" id="formFile">
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <button class="btn btn-primary btn-sm bg-gradient rounded-0" form="file-upload"><i class="fa fa-save"></i> Save File</button>
                            <button class="btn btn-light border btn-sm bg-gradient rounded-0" type="reset" form="file-upload"><i class="fa fa-times"></i> Reset</button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 col-md-6 col-sm-12 col-xs-12">
                    <div class="card rounded-0 shadow">
                        <div class="card-header">
                            <div class="card-title h4 mb-0 fw-bolder">Uploaded Files</div>
                        </div>
                        <div class="card-body">
                            <div class="container-fluid">
                                <table class="table table-striped table-bordered">
                                    <colgroup>
                                        <col width="10%">
                                        <col width="20%">
                                        <col width="50%">
                                        <col width="20%">
                                    </colgroup>
                                    <thead>
                                        <tr class="bg-primary bg-gradient text-light">
                                            <th class="p-1 text-center">#</th>
                                            <th class="p-1 text-center">Label</th>
                                            <th class="p-1 text-center">Path</th>
                                            <th class="p-1 text-center"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $i = 1;
                                        foreach($uploads as $row):
                                            $row['fname'] = str_replace("uploads/","", $row['path']);
                                        ?>
                                        <tr>
                                            <td class="px-2 py-1 align-middle text-center"><?= number_format($i++) ?></td>
                                            <td class="px-2 py-1 align-middle"><?= $row['label'] ?></td>
                                            <td class="px-2 py-1 align-middle"><p class="m-0 text-truncate" title="<?= $row['fname'] ?>"><?= $row['fname'] ?></p></td>
                                            <td class="px-2 py-1 align-middle text-center">
                                                <a href="<?= $row['path'] ?>" class="text-muted text-decoration-none mx-2" target="_blank" title="View File"><i class="fa fa-external-link"></i></a>
                                                <a href="<?= base_url($row['path']) ?>" class="text-primary fw-bolder text-decoration-none mx-2" target="_blank" title="Download File" download="<?= $row['fname'] ?>"><i class="fa fa-download"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if(!isset($uploads) || (isset($uploads) && count($uploads) <= 0)): ?>
                                            <tr>
                                                <th colspan="4" class="p-1 text-center">No records found</th>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</body>
<?= $this->renderSection('custom_js') ?>
</html>