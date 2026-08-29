<?php $this->layout("layouts/erp"); ?>
<?php if (!$condo->select): ?>
    <?php $this->insert("/pages/welcome-condo"); ?>
<?php else: ?>
    <div class="container">
        <?php $this->insert("components/finance/sidebar"); ?>


        <div class="page_main">
            <h3>Controle Mensal</h3>
            <br>
            <div id="control"></div>
        </div>
        <div class="page_main">
            <div class="page_widget">
                <div class="widget_balance">
                    <div class="icon-circle orange"><span> À pagar</span></div>
                    <br>
                    <table id="contact-table">
                        <thead>
                        <tr>
                            <th></th>
                            <th class="sortable asc" data-column="0">Cliente <span class="sort-arrow"></span></th>
                            <th class="sortable asc" data-column="3">Indentificador <span class="sort-arrow"></span></th>
                            <th class="sortable asc" data-column="6">Valor <span class="sort-arrow"></span></th>
                            <th>Ação</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($expense)): ?>
                            <?php foreach ($expense as $expenseItem): ?>
                                <?= $this->insert("pages/balance", ["invoice" => $expenseItem->data()]); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="message error al-center icon-check-square-o">
                                No momento, não existem contas a pagar.
                            </div>
                        <?php endif; ?>

                        </tbody>
                    </table>
                </div>
                <div class="widget_balance">
                    <div class="icon-circle blue"><span> À receber</span></div>
                    <br>
                    <table id="contact-table">
                        <thead>
                        <tr>
                            <th></th>
                            <th class="sortable asc" data-column="0">Cliente <span class="sort-arrow"></span></th>
                            <th class="sortable asc" data-column="3">Indentificador <span class="sort-arrow"></span></th>
                            <th class="sortable asc" data-column="6">Valor <span class="sort-arrow"></span></th>
                            <th>Ação</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($income)): ?>
                            <?php foreach ($income as $incomeItem): ?>
                                <?= $this->insert("pages/balance", ["invoice" => $incomeItem->data()]); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="message info al-center icon-check-square-o">
                                No momento, não existem contas a receber.
                            </div>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>



    <?php $this->start("scripts"); ?>

    <script type="text/javascript">
        $(function () {
            Highcharts.setOptions({
                lang: {
                    decimalPoint: ',',
                    thousandsSep: '.'
                }
            });

            var chart = Highcharts.chart('control', {
                chart: {
                    type: 'areaspline',
                    spacingBottom: 0,
                    spacingTop: 5,
                    spacingLeft: 0,
                    spacingRight: 0,
                    height: (9 / 32 * 100) + '%'
                },
                title: null,
                xAxis: {
                    categories: [<?= $chart->categories; ?>],
                    minTickInterval: 1
                },
                yAxis: {
                    allowDecimals: true,
                    title: null,
                },
                tooltip: {
                    shared: true,
                    valueDecimals: 2,
                    valuePrefix: 'R$ '
                },
                credits: {
                    enabled: false
                },
                plotOptions: {
                    areaspline: {
                        fillOpacity: 0.5
                    }
                },
                series: [{
                    name: 'Receitas',
                    data: [<?= $chart->income;?>],
                    color: '#00A759',
                    lineColor: '#008745'
                }, {
                    name: 'A receber',
                    data: [<?= $chart->receive;?>],
                    color: '#0067b8',
                    lineColor: '#0078d4'
                }, {
                    name: 'Despesas',
                    data: [<?= $chart->expense;?>],
                    color: '#B30000',
                    lineColor: '#D90000'
                //}, {
                //    name: 'Inadimplência',
                //    data: [<?php //= $chart->owing;?>//],
                //    color: '#FFD24D',
                //    lineColor: '#F5B946'
                }]
            });
        });
    </script>
    <?php $this->end(); ?>

<?php endif; ?>
