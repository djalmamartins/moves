<!--MODALS-->
<div class="app_modal" id="rmv">
    <div class="close" data-modalclose="true">
        <svg viewBox="0 0 24 24" focusable="false" height="24" width="24" jsname="lZmugf">
            <style type="text/css">
                .x {
                    fill: #FFFFFF;
                }
            </style>
            <path d="M0 0h24v24H0z" fill="none">
            </path>
            <path class="x"
                  d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path>
        </svg>
    </div>

    <!--REGISTER UNITS-->
    <section class="app_modal_box app_modal_register_units" id="rmv">
        <?= $this->insert("components/condo/units_modal", $this->data); ?>
    </section>

    <!--REGISTER OWNER-->
    <section class="app_modal_box app_modal_register_owner" id="rmv">
        <?= $this->insert("components/condo/owner_modal", $this->data); ?>
    </section>


</div>
