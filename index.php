<!DOCTYPE html>
<html>
    <head>
        <title>Business Search</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" />
        <link rel="stylesheet" href="style.css" />
    </head>
    <body>
        
        <hr />
        <h1 class="text-center">Công cụ tự động lấy dữ liệu trên [<em>kepler.sos.ca.gov</em>]</h1>
        <hr />
        
        <div class="container">
            <div class="row">
                <div class="col-lg-5">
                    <label>Search Type</label>
                    <p><input type="radio" name="s_type" value="1" /> Corporation Name</p>
                    <p><input type="radio" name="s_type" value="2" /> Limited Liability Company/Limited Partnership Name</p>
                    <p><input type="radio" name="s_type" value="3" /> Entity Number</p>
                    
                    <div class="input-group">
                        <span class="input-group-addon"><a class="ajax"></a></span>
                        <input type="text" id="keyword" class="form-control" />
                        <div class="input-group-btn">
                            <button class="btn btn-primary btn-search"><i class="glyphicon glyphicon-search"></i> Search</button>
                            <button class="btn btn-danger btn-s-stop"><i class="glyphicon glyphicon-off"></i> Stop</button>
                            <button class="btn btn-default btn-test"><i class="glyphicon glyphicon-off"></i> Test</button>
                        </div>
                    </div>
                    
                    <div class="msg"></div>
                    
                </div>
                
                <div class="col-lg-7">
                
                    <h4>My Search Sessions</h4>
<div class="sess-list">
<?php include 'sess_list.php'; ?>
</div>
                    <button class="btn btn-success btn-sess-reload">Refresh <i class="glyphicon glyphicon-refresh"></i></button>
                    <button class="btn btn-primary btn-s-continue">Continue this session <i class="glyphicon glyphicon-chevron-right"></i></button>
                    <button class="btn btn-danger btn-delete-sess"><i class="glyphicon glyphicon-remove"></i> Delete selected</button>
                    <button class="btn btn-success btn-export-excel"><i class="glyphicon glyphicon-download-alt"></i> Export to excel</button>
                </div>
            </div>

        </div>
    
    <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="script.js"></script>
    <script src="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    </body>
</html>