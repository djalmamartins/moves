<?php $this->layout("layouts/erp"); ?>
<?php if (!$condo->select): ?>
    <?php $this->insert("/pages/welcome-condo"); ?>
<?php else: ?>
    <div class="container">
        <?php $this->insert("components/finance/sidebar"); ?>

        <div class="page_main">
            <?php $this->insert("components/finance/sidebar-filtro"); ?>

            <table id="contact-table">
                <thead>
                <tr>
                    <th></th>
                    <th class="sortable asc" data-column="0">Cliente <span class="sort-arrow"></span></th>
                    <th class="sortable asc" data-column="1">Cobrança <span class="sort-arrow"></span></th>
                    <th class="sortable asc" data-column="2">Referência <span class="sort-arrow"></span></th>
                    <th class="sortable asc" data-column="3">Indentificador <span class="sort-arrow"></span></th>
                    <th class="sortable asc" data-column="4">Emissão <span class="sort-arrow"></span></th>
                    <th class="sortable asc" data-column="5">Vencimento <span class="sort-arrow"></span></th>
                    <th class="sortable asc" data-column="6">Valor <span class="sort-arrow"></span></th>
                    <th class="sortable asc" data-column="7">Status <span class="sort-arrow"></span></th>
                    <th>Ação</th>
                </tr>
                </thead>
                <tbody>

                <tr>
                    <td class="icon-file-text-o"></td>
                    <td><span class="name">Vinicius Moura</span>
                        <span class="doc">993.493.456-68</span></td>
                    <td>Simples</td>
                    <td>Fev24</td>
                    <td>1234566655</td>
                    <td>01/02/2024</td>
                    <td>12/02/2024</td>
                    <td>R$ 170,00</td>
                    <td><span class="tag_warning">Atrasado</span>
<!--                        <span class="doc"></span></td>-->
                    <td><div class="icon-circle-thin"></div></td>
                </tr>
                <tr>
                    <td class="icon-file-text-o"></td>
                    <td><span class="name">José Andrade</span>
                        <span class="doc">993.493.456-68</span></td>
                    <td>Simples</td>
                    <td>Fev24</td>
                    <td>1234566655</td>
                    <td>01/02/2024</td>
                    <td>12/02/2024</td>
                    <td>R$ 170,00</td>
                    <td><span class="tag_green">Recebido</span>
                        <span class="doc">14/02/2024</span></td>
                    <td><div class="icon-check-circle green"></div></td>
                </tr>
                <tr>
                    <td class="icon-file-text-o"></td>
                    <td><span class="name">Rodrigo Costa</span>
                        <span class="doc">993.493.456-69</span></td>
                    <td>Simples</td>
                    <td>Fev24</td>
                    <td>1234566655</td>
                    <td>01/02/2024</td>
                    <td>12/02/2024</td>
                    <td>R$ 170,00</td>
                    <td><span class="tag_green">Recebido</span>
                        <span class="doc">14/02/2024</span></td>
                    <td><div class="icon-check-circle green"></div></td>
                </tr>
                </tbody>
            </table>
            <?php $this->start("scripts"); ?>
            <script>

            </script>

            <?php $this->end(); ?>
        </div>

    </div>



<?php endif; ?>
