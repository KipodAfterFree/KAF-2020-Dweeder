FROM node:14.10
# Update indexes
RUN apt-get update
# Install chrome
RUN apt-get install -y ca-certificates
# Install libraries
RUN apt-get install -y libappindicator1 libasound2 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 libnss3 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6
# Install tools
RUN apt-get install -y gconf-service lsb-release wget xdg-utils
# Install fonts
RUN apt-get install -y fonts-liberation
# Switch directory
WORKDIR /home
# Copy package.json
COPY code/package.json package.json
# Install dependencies
RUN npm install
# Copy code
COPY code .
# Start application
CMD ["node", "--trace-warnings", "application.mjs"]