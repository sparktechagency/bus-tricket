# FROM nginx:stable-alpine
# COPY default.conf /etc/nginx/nginx.conf

FROM nginx:stable-alpine
RUN rm /etc/nginx/conf.d/default.conf
COPY default.conf /etc/nginx/conf.d/default.conf
