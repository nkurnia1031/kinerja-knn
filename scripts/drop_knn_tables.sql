USE `2026_04_kinerja_knn`;

DROP PROCEDURE IF EXISTS `sp_get_analisa_terakhir`;
DROP VIEW IF EXISTS `v_knn_ringkasan`;

DROP TABLE IF EXISTS `knn_detail_jarak`;
DROP TABLE IF EXISTS `knn_hasil_klasifikasi`;
DROP TABLE IF EXISTS `knn_data_training`;
DROP TABLE IF EXISTS `knn_statistik`;
DROP TABLE IF EXISTS `knn_analisa`;
