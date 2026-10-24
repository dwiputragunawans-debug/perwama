{{--
    Perangkap bot: kolom di luar layar, tidak dapat difokuskan, dan tidak
    pernah diisi pengunjung sungguhan. Bila terisi, formulir dianggap spam
    (lihat App\Support\Honeypot) dan datanya tidak disimpan.
--}}
<div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
    <label for="website">Website</label>
    <input id="website" type="text" name="{{ \App\Support\Honeypot::FIELD }}" value="" tabindex="-1" autocomplete="off">
</div>
