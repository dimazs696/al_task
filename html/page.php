<html>
    <meta charset="UTF-8">
    <head>
        <link rel="stylesheet" href="/assets/css/bootstrap.css">
        <link rel="stylesheet" href="/assets/css/custom.css">
        <script src="/assets/scripts/jquery-3.7.1.min.js" type="text/javascript"></script>
        <title>Поиск текста по картинкам.</title>
    </head>
    <body>
        <div class="container mt-3">
            <div class="row">
                <!-- Image -->
                <div class="col-xl-7 mb-3">
                    <div class="mb-3">
                        <button type="button" data-mode="generate" class="btn btn-primary">Сгенерировать</button>
                        <button type="button" data-mode="find_coordinates" class="btn btn-success">Найти</button>
                    </div>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div id="image_content" class="position-relative">
                                <div class="border border-danger" id="point">
                                    <span id="center"></span>
                                </div>
                                <img src="<?php echo ((!is_null($image->image_content)) ? $image->image_content : '/assets/images/no_image.png'); ?>" class="rounded border w-100 mb-2">
                            </div>
                            <span class="fw-semibold">Изображение:</span>
                            <span class="text-muted" id="filename">
                                <?php if (!is_null($image->file_name)) echo $image->file_name.'.png'; ?>
                            </span>
                        </div>
                    </div>

                </div>
                <!-- /image -->

                <!-- Analyze -->
                <div class="col-xl-5 mb-3">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Сравнение</h6>
                        </div>
                        <div class="card-body">
                            <div class="p-1 mb-1 fw-semibold border-bottom">
                                Найденные координаты
                            </div>

                            <div class="row">
                                <div class="col-4 mb-2">
                                    X
                                </div>
                                <div class="col-4 mb-2 text-muted" id="x_find_pos">

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4 mb-2">
                                    Y
                                </div>
                                <div class="col-4 mb-2 text-muted" id="y_find_pos">

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4 mb-2">
                                    Расстояние
                                </div>
                                <div class="col-4 mb-2 text-muted" id="distance">

                                </div>
                            </div>

                            <div class="p-1 mb-1 fw-semibold border-bottom">
                                Истинные координаты
                            </div>

                            <div class="row">
                                <div class="col-4 mb-2">
                                    X
                                </div>
                                <div class="col-4 mb-2 text-muted" id="x_pos">
                                    <?php if (!is_null($image->x_pos)) echo $image->x_pos; ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4 mb-2">
                                    Y
                                </div>
                                <div class="col-4 mb-2 text-muted" id="y_pos">
                                    <?php if (!is_null($image->y_pos)) echo $image->y_pos; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">История</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                Выводить элементов:
                                <select onchange="pagination = this.value; loadHistory();" class="form-control">
                                    <option value="10" selected>10</option>
                                    <option value="20">20</option>
                                    <option value="30">30</option>
                                    <option value="40">40</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                            <table class="table">
                                <thead>
                                <tr>
                                    <th width="20%">X</th>
                                    <th width="20%">Y</th>
                                    <th width="20%">DX</th>
                                    <th width="20%">DY</th>
                                    <th width="20%">D</th>
                                </tr>
                                </thead>

                                <tbody>
                                    <?php if (count($image->history)){
                                        $a = 0;
                                        foreach ($image->history as $item){
                                            if ($a == 10) break;
                                            $a++;
                                            echo '
                                                <tr>
                                                    <td>'.$item['x'].'</td>
                                                    <td>'.$item['y'].'</td>
                                                    <td>'.($image->x_pos - $item['x']).'</td>
                                                    <td>'.($image->y_pos - $item['y']).'</td>
                                                    <td>'.$item['distance'].'</td>
                                                </tr>';
                                        }
                                    }?>
                                </tbody>
                            </table>
                            <ul class="pagination">
                                <li class="page-item active">
                                    <a href="#" class="page-link">1</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /analyze -->
            </div>
        </div>

        <script>

            let page = 1;
            let pagination = 10;

            let x = <?php echo ($image->x_pos) ?? 0; ?>;
            let y = <?php echo ($image->y_pos) ?? 0; ?>;

            let history = <?php echo json_encode($image->history)?>;
            let total_pages =  Math.ceil(history.length / pagination);

            //
            // Load pagination bbuttons
            //
            function loadPagination()
            {
                $('.pagination').html('');
                total_pages =  Math.ceil(history.length / pagination);
                html = `
                    <li class="page-item ${page === 1 ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="page = 1; loadHistory();">1</a>
                    </li>
                `;

                if (total_pages > 1){

                    if (page - 1 > 1) {
                        html += `
                            <li class="page-item">
                                <a class="page-link" href="#" onclick="page = page - 1; loadHistory();">${page - 1}</a>
                            </li>
                        `;
                    }

                    if (page !== 1 && page !== total_pages) {
                        html += `
                            <li class="page-item active">
                                <a class="page-link" href="#">${page}</a>
                            </li>
                        `;
                    }

                    if (page + 1 < total_pages) {
                        html += `
                            <li class="page-item">
                                <a class="page-link" href="#" onclick="page = page + 1; loadHistory();">${page + 1}</a>
                            </li>
                        `;
                    }

                    if (total_pages > 1) {
                        html += `
                            <li class="page-item ${page === total_pages ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="page = total_pages; loadHistory();">${total_pages}</a>
                            </li>
                        `;
                    }

                }
                $('.pagination').html(html);
            }

            //
            // Load history.
            //
            function loadHistory()
            {
                $('tbody').html('');
                loadPagination();

                if (history.length === 0) return;

                var first = (page - 1) * pagination;
                var last = first + pagination;
                var items = history.slice(first,last)

                var html = '';

                $(items).each(function (index, item) {
                    html += `
                        <tr>
                            <td>${item.x}</td>
                            <td>${item.y}</td>
                            <td>${x - item.x}</td>
                            <td>${y - item.y}</td>
                            <td>${item.distance}</td>
                        </tr>
                    `;
                });
                $('tbody').html(html);

            }

            $(document).ready(function (){
                loadPagination();
                $('#point').hide();
                //
                // Generate image.
                //
                $('.btn').click(function (e){
                    $('#point').hide();
                    var mode = $(e.target).data('mode')
                    $.ajax({
                        url: '/',
                        method: 'post',
                        data:{
                            mode:mode
                        },
                        async: false,
                        success: function (response){
                            switch (mode){
                                case 'generate':{
                                    history = [];
                                    loadHistory();
                                    $('img').attr('src',response.content)
                                    $('#filename').html(response.filename + '.png')
                                    $('#x_pos').html(response.x_true);
                                    $('#y_pos').html(response.y_true);
                                    break;
                                }
                                case 'find_coordinates':{
                                    history = response.history
                                    loadHistory();

                                    var x = response.x_find;
                                    var y = response.y_find;
                                    var img = document.querySelector('img');
                                    var size = img.width;

                                    $('#point').css('top',((size / 1000) * (y)) - 10)
                                    $('#point').css('left',((size / 1000) * (x)) - 10)
                                    $('#point').show();
                                    $('#x_find_pos').html(x);
                                    $('#y_find_pos').html(y);
                                    $('#distance').html(response.distance);
                                    break;
                                }
                            }
                        }
                    })
                });
            })

        </script>
    </body>
</html>