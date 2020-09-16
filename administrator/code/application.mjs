import http from 'http';
import puppeteer from 'puppeteer';

const browser = await puppeteer.launch({
    headless: true,
    args: ["--no-sandbox"]
});

http.createServer(async function (request, response) {
    // Parse the request
    let searchParams = new URLSearchParams(request.url.split("/").pop());
    // Make sure the parameters contain a visit address
    if (searchParams.has("visit")) {
        // Copy the visit url
        let url = searchParams.get("visit");
        // Launch puppet
        let page = await browser.newPage();
        // Go to page
        await page.goto(new URL(url));
        // Add to localStorage
        page.evaluate(() => {
            window.localStorage.setItem("token", "eyJleHBpcnkiOm51bGwsImNvbnRlbnQiOnsibmFtZSI6IktBRntfdzNsbF90aDR0NV93aDR0X2I0ZF9jMGQzX2wwMGs1X2wxa2V9IiwiaGFuZGxlIjoic2h1a3kifX0=:V0HhP4AicOqHQdqnUT/cDgAV/WbanoUdwaUBw2RsdUg=");
        });
        // Goto page
        await page.goto(new URL(url));
        // Write output
        response.write("OK");
    } else {
        response.write("No 'visit' parameter")
    }
    response.end();
}).listen(80);

