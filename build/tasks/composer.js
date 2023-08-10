import {runSetup} from "../utils.js";
import fs from "fs";
import {join} from "path";

export const name = "Install composer";

export default async function (exit) {
	const composerPharPath = join(DIR, "../composer.phar");
	if (!fs.existsSync(composerPharPath)) {
		try {
			await runSetup();
		} catch (e) {
			exit(e);
		}
	} else if (!fs.statSync(composerPharPath).isFile()) exit("Expected composer.phar to be a file!");
};