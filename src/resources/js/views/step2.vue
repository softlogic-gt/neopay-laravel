<template>
    <div>
        <iframe name="ddc-iframe" title="3DS Device Data Collection" width="1" height="1" aria-hidden="true"> </iframe>
        <form id="ddc-form" method="POST" target="ddc-iframe" :action="action">
            <input type="hidden" name="JWT" :value="token" />
        </form>
    </div>
</template>
<script>
export default {
    props: ["action", "token"],
};
</script>
