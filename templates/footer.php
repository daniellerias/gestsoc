<footer>
<div class="footer-content">
<span><a href="<?= $base_url ?>admin/version.php"><?= APP_NAME ?> - v<?= VERSION ?></a>
<p><?php echo date('d/m/Y'); ?> - <span id="current-time"></span></p>
</span>

</div>
</footer>
<script>
function updateTime() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('current-time').textContent = `${hours}:${minutes}:${seconds}`;
}
updateTime();
setInterval(updateTime, 1000);
</script>
</body>
</html>