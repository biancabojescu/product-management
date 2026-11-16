<dialog id="warningModal" class="warning-modal" role="alertdialog" aria-labelledby="warningModalTitle" aria-describedby="warningModalMessage">
    <header class="warning-header">
        <div class="warning-heading">
            <span class="warning-icon" aria-hidden="true">!</span>
            <span>Warning Modal</span>
        </div>
        <button class="warning-close" type="button" data-cancel-warning aria-label="Close confirmation">&times;</button>
    </header>
    <div class="warning-content">
        <h2 id="warningModalTitle" data-warning-title>Are you sure?</h2>
        <p id="warningModalMessage" data-warning-message>You have unsaved changes. If you close this form, your changes will be lost.</p>
    </div>
    <div class="warning-actions">
        <button class="button button-secondary" type="button" data-cancel-warning autofocus>Keep editing</button>
        <button class="button warning-confirm" type="button" data-confirm-warning>Discard changes</button>
    </div>
</dialog>
