FROM payfortstart/woocommerce-app:configured

RUN mkdir ./wp-content/plugins/payfort
COPY . ./wp-content/plugins/payfort
RUN chown -R www-data:www-data ./wp-content/plugins/payfort
