<?php

defined('C5_EXECUTE') or die('Access denied');

use Concrete\Core\Form\Service\Form;
use Concrete\Core\Form\Service\Widget\PageSelector;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Core\View\View;

/** @var array $pageRedirects */
$pageRedirects = $pageRedirects ?? [];

$app = Application::getFacadeApplication();
/** @var Form $form */
/** @noinspection PhpUnhandledExceptionInspection */
$form = $app->make(Form::class);
/** @var Token $token */
/** @noinspection PhpUnhandledExceptionInspection */
$token = $app->make(Token::class);
/** @var PageSelector $pageSelector */
/** @noinspection PhpUnhandledExceptionInspection */
$pageSelector = $app->make(PageSelector::class);

?>

<div class="ccm-dashboard-header-buttons">
    <div class="btn-group" role="group">
        <?php /** @noinspection PhpUnhandledExceptionInspection */
        View::element("dashboard/help", [], "redirector"); ?>

        <a href="javascript:void(0);" id="ccm-add-item" class="btn btn-primary">
            <?php echo t("Add Redirection"); ?>
        </a>
    </div>
</div>

<?php \Concrete\Core\View\View::element("dashboard/did_you_know", [], "redirector"); ?>

<form action="#" method="post">´
    <?php echo $token->output("update_settings"); ?>

    <fieldset>
        <legend>
            <?php echo t("Redirections"); ?>
        </legend>

        <div id="no-entries-container" class="<?php echo count($pageRedirects) > 0 ? "d-none" : "" ?>">
            <?php echo t("Currently, you don't have any redirects. Click on 'Add Redirection' in the toolbar to create a redirect rule."); ?>
        </div>

        <table class="table table-striped <?php echo count($pageRedirects) > 0 ? "" : "d-none" ?>"
               id="page-redirects-container">
            <thead>
            <tr>
                <th>
                    <?php echo t("Old Path"); ?>
                </th>

                <th>
                    <?php echo t("New Page"); ?>
                </th>

                <th>
                    &nbsp;
                </th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($pageRedirects as $index => $pageRedirect) { ?>
                <tr data-index="<?php echo h($index); ?>">
                    <th>
                        <?php echo $form->text("pageRedirects[" . $index . "][oldPath]", $pageRedirect["oldPath"] ?? null, ["placeholder" => t("/old-page-to-redirect.html")]); ?>
                    </th>

                    <th>
                        <?php echo $pageSelector->selectPage("pageRedirects[" . $index . "][cID]", $pageRedirect["cID"] ?? null); ?>
                    </th>

                    <th>
                        <div class="float-end">
                            <a href="javascript:void(0);" class="btn btn-danger btn-remove">
                                <?php echo t("Remove Redirection"); ?>
                            </a>
                        </div>
                    </th>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </fieldset>

    <script id="redirector-row-template" type="text/template">
        <tr data-index="<%=index%>">
            <th>
                <!--suppress HtmlFormInputWithoutLabel -->
                <input name="pageRedirects[<%=index%>][oldPath]" type="text" class="form-control"
                       placeholder="<?php echo h(t("/old-page-to-redirect.html")); ?>">
            </th>

            <th>
                <div data-concrete-page-input="index-<%=index%>">
                    <!--suppress HtmlUnknownTag -->
                    <concrete-page-input
                            choose-text="<?php echo h(t('Choose Page')) ?>"
                            input-name="pageRedirects[<%=index%>][cID]"
                    ></concrete-page-input>
                </div>
            </th>

            <th>
                <div class="float-end">
                    <a href="javascript:void(0);" class="btn btn-danger btn-remove">
                        <?php echo t("Remove Redirection"); ?>
                    </a>
                </div>
            </th>
        </tr>
    </script>

    <!--suppress JSUnresolvedFunction, JSUnresolvedVariable -->
    <script>
        (function ($) {
            $(function () {
                const $pageRedirectsContainer = $("#page-redirects-container");
                const $noEntriesContainer = $("#no-entries-container");

                function findNextFreeIndex() {
                    let rows = $pageRedirectsContainer.find('tbody tr');
                    let indices = [];

                    rows.each(function () {
                        let index = $(this).data('index');

                        if (index !== undefined) {
                            indices.push(index);
                        }
                    });

                    indices.sort(function (a, b) {
                        return a - b;
                    });

                    let nextIndex = 0;

                    for (let i = 0; i < indices.length; i++) {
                        if (indices[i] > nextIndex) {
                            break;
                        }

                        nextIndex = indices[i] + 1;
                    }

                    return nextIndex;
                }

                let addRow = function (e) {
                    e.stopPropagation();
                    e.preventDefault();

                    $noEntriesContainer.addClass("d-none");
                    $pageRedirectsContainer.removeClass("d-none");

                    let index = findNextFreeIndex();
                    let $newRow = $(_.template($("#redirector-row-template").html())({
                        index: index
                    }));

                    Concrete.Vue.activateContext('cms', function (Vue, config) {
                        new Vue({
                            el: 'div[data-concrete-page-input="index-' + index + '"]',
                            components: config.components
                        })
                    })

                    $newRow.find(".btn-remove").on("click", deleteRow);

                    $pageRedirectsContainer.find("tbody").append($newRow);

                    return false;
                }

                let deleteRow = function (e) {
                    e.stopPropagation();
                    e.preventDefault();

                    let $row = $(this).closest("tr");

                    ConcreteAlert.confirm(
                        <?php echo json_encode(t('Are you sure?')); ?>,
                        function () {
                            $row.remove();

                            if ($pageRedirectsContainer.find("tbody tr").length === 0) {
                                $pageRedirectsContainer.addClass("d-none");
                                $noEntriesContainer.removeClass("d-none");
                            } else {
                                $pageRedirectsContainer.removeClass("d-none");
                                $noEntriesContainer.addClass("d-none");
                            }
                            $(".ui-dialog-content").dialog("close");
                        }
                    );

                    console.log(modal);


                    return false;
                };

                $pageRedirectsContainer.find(".btn-remove").on("click", deleteRow);
                $("#ccm-add-item").on("click", addRow);
            });
        })(jQuery);
    </script>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <?php echo $form->submit('save', t('Save'), ['class' => 'btn btn-primary float-end']); ?>
        </div>
    </div>
</form>
