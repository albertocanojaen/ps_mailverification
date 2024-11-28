{if isset($success) && $success}
    <div class="alert alert-success">
        <p>Your account has been successfully verified. You can now <a href="{$link->getPageLink('authentication')}">log in</a>.</p>
    </div>
{else}
    <div class="alert alert-danger">
        <p>{if isset($errors)}{$errors[0]}{/if}</p>
    </div>
{/if}
