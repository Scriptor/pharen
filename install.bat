@echo OFF
SET pharen_bin_dir=%~dp0
setx PATH "%pharen_bin_dir%bin;%path%;"
echo "Pharen successfully installed."
