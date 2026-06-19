FROM nginx:1.27-bookworm

ARG ENV=prod
COPY default.conf /etc/nginx/conf.d/default.conf
COPY default.dev.conf /etc/nginx/conf.d/default.dev.conf

RUN if [ "$ENV" = "dev" ]; then \
    cp /etc/nginx/conf.d/default.dev.conf /etc/nginx/conf.d/default.conf; \
    fi

EXPOSE 80 443