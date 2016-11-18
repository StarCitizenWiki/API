<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Wiki Scripts by FoXFTW</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" media="screen" title="no title">
    </head>
    <body>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4 col-md-offset-4">
                    <h4>Star Citizen Wiki Scripts</h4>
                    <ul class="list-group">
                        <?php
                        foreach ($Router->getRoutes() as $route) {
                            echo '<li class="list-group-item"><a href="'.$route[1].'">'.$route[3].'</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <br><br>
        </div>
    </body>
</html>
