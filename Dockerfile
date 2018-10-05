# Copyright 2018 OpenCensus Authors
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

FROM circleci/php:7.2-node

COPY . /workspace
WORKDIR /workspace

# current user is circleci
RUN sudo chown -R $(whoami) /workspace
RUN sudo apt-get update && sudo apt-get install -y libgmp-dev re2c libmhash-dev libmcrypt-dev file && sudo ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/local/include/
RUN sudo docker-php-ext-install bcmath gmp sockets

RUN composer install -n --prefer-dist

ENTRYPOINT []
