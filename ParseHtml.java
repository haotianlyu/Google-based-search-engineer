package csci572hw5;

import java.io.BufferedWriter;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileWriter;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

import org.apache.tika.metadata.Metadata;
import org.apache.tika.parser.ParseContext;
import org.apache.tika.parser.html.HtmlParser;
import org.apache.tika.sax.BodyContentHandler;

public class ParseHtml {

	public static void main(String[] args) throws Exception {
		FileInputStream inputstream;
		List<String> urls = new ArrayList<String>();
		File htmlfiles = new File("D:/ShareFolder/WP/WP/WP");
		int count = 0;
		for (File file : htmlfiles.listFiles()) {
			inputstream = new FileInputStream(file);
			BodyContentHandler handler = new BodyContentHandler(-1);
			Metadata metadata = new Metadata();
			ParseContext pcontext = new ParseContext();
			HtmlParser htmlparser = new HtmlParser();
			htmlparser.parse(inputstream, handler, metadata, pcontext);
			urls.add(handler.toString());
			count ++;
			System.out.println(count);
		}
		FileWriter fileWriter = null;
		BufferedWriter bufferWriter = null;
		try {
			fileWriter = new FileWriter("D:/ShareFolder/WP/big.txt");
			bufferWriter = new BufferedWriter(fileWriter);
			for (String s : urls) {
				bufferWriter.write(s);
				bufferWriter.write("\n");
				count --;
				System.out.println(count);
			}
		} catch (Exception e) {
			e.printStackTrace();
		} finally {
			try {
				bufferWriter.close();
				fileWriter.close();
			} catch (IOException e) {
				e.printStackTrace();
			}
		}
	}

}
