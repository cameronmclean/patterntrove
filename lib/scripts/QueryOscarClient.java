import uk.ac.cam.ch.wwmm.oscar.Oscar;
import uk.ac.cam.ch.wwmm.oscar.chemnamedict.entities.*;
import java.util.*;
import java.io.*;
import java.net.Socket;

public class QueryOscarClient {

	public static void main(String[] args) {
		try { 
			String content = new String();
			BufferedReader fileInput = new BufferedReader(new FileReader(args[0]));
			while (fileInput.ready()) {
				content = content.concat(fileInput.readLine());
			}	
			fileInput.close();
			if (content.length() > 0) {
				Socket connection = new Socket("127.0.0.1", 5642);
				ObjectOutputStream output = new ObjectOutputStream(connection.getOutputStream());
				BufferedReader resultsReader = new BufferedReader(new InputStreamReader(connection.getInputStream()));
				Vector<String> contentObject = new Vector<String>();
				contentObject.addElement(content);
				output.writeObject(contentObject);
				String result = resultsReader.readLine();
		            	while (!(result == null || result.equals(".."))){	
					System.out.println(result);
					result = resultsReader.readLine();
				}
            			output.close();
                        	resultsReader.close();
				connection.close();
			}
		}
		catch (IOException ioe) {
			ioe.printStackTrace(System.err);
        	}
	}
}		
